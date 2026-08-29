<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCA\StickyNotes\Db\Note;
use OCA\StickyNotes\Db\NoteMapper;
use OCA\StickyNotes\Db\Share;
use OCA\StickyNotes\Db\ShareMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;
use OCA\StickyNotes\Service\NotificationService;

class NoteController extends Controller {
    public function __construct(
        IRequest $request,
        private NoteMapper $noteMapper,
        private ShareMapper $shareMapper,
        private IUserSession $userSession,
        private IGroupManager $groupManager,
        private NotificationService $notifications,
        private IUserManager $userManager,
        private LoggerInterface $logger,
    ) { parent::__construct(Application::APP_ID, $request); }

    private function uid(): string { return $this->userSession->getUser()?->getUID() ?? ''; }
    private function groups(): array { return array_keys($this->groupManager->getUserGroupIds($this->userSession->getUser())); }


    private function normalizeAssignment(?string $assignedUid): ?string {
        $assignedUid = trim((string)$assignedUid);
        if ($assignedUid === '') {
            return null;
        }
        if (str_starts_with($assignedUid, 'group:')) {
            $gid = substr($assignedUid, 6);
            return ($gid !== '' && $this->groupManager->groupExists($gid)) ? 'group:' . $gid : null;
        }
        return $this->userManager->userExists($assignedUid) ? $assignedUid : null;
    }

    private function safeNotifyAssignment(Note $note, ?string $target): void {
        if ($target === null) return;
        try {
            $event = str_starts_with($target, 'group:')
                ? NotificationService::EVENT_ASSIGNED_GROUP
                : NotificationService::EVENT_ASSIGNED_USER;
            foreach ($this->notifications->recipientsForAssignment($note) as $recipient) {
                $this->notifications->send($note, $recipient, $event, $this->uid());
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Sticky Notes assignment notification failed', [
                'app' => Application::APP_ID, 'exception' => $e,
                'noteId' => $note->getId(), 'assignedUid' => $target,
            ]);
        }
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $notes = $this->noteMapper->findAllForUser($this->uid(), $this->groupManager->getUserGroupIds($this->userSession->getUser()));
        $result = [];
        foreach ($notes as $note) {
            $row = $note->jsonSerialize();
            $row['shares'] = $this->shareMapper->findByNote((int)$note->getId());
            $result[] = $row;
        }
        return new DataResponse($result);
    }

    #[NoAdminRequired]
    public function create(string $title = '', string $content = '', string $color = '#4f86f7', ?int $categoryId = null, string $type = 'note', string $priority = 'normal', ?string $assignedUid = null, ?int $dueAt = null): DataResponse {
        $now = time();
        $note = new Note();
        $note->setOwnerUid($this->uid());
        $note->setTitle(mb_substr(trim($title), 0, 255));
        $note->setContent($content);
        $note->setColor(preg_match('/^#[0-9a-fA-F]{6}$/',$color)?strtolower($color):'#4f86f7');
        $note->setCategoryId($categoryId ?: null);
        $note->setType($type === 'task' ? 'task' : 'note');
        $note->setPriority(in_array($priority, ['normal','important'], true) ? $priority : 'normal');
        $assignedUid = $this->normalizeAssignment($assignedUid);
        $note->setAssignedUid($assignedUid);
        $note->setDueAt($dueAt);
        $note->setCreatedAt($now);
        $note->setUpdatedAt($now);
        $saved = $this->noteMapper->insert($note);
        $this->safeNotifyAssignment($saved, $assignedUid);
        return new DataResponse($saved, Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    public function update(int $id, ?string $title = null, ?string $content = null, ?string $color = null, ?int $categoryId = null, ?string $type = null, ?string $priority = null, ?string $assignedUid = null, ?int $dueAt = null): DataResponse {
        try { $note = $this->noteMapper->findForUser($id, $this->uid()); } catch (DoesNotExistException) { return new DataResponse(['error'=>'Not found'], Http::STATUS_NOT_FOUND); }
        if ($title !== null) $note->setTitle(mb_substr(trim($title), 0, 255));
        if ($content !== null) $note->setContent($content);
        if ($color !== null && preg_match('/^#[0-9a-fA-F]{6}$/',$color)) $note->setColor(strtolower($color));
        if ($categoryId !== null) $note->setCategoryId($categoryId ?: null);
        if ($type !== null) $note->setType($type === 'task' ? 'task' : 'note');
        if ($priority !== null && in_array($priority, ['normal','important'], true)) $note->setPriority($priority);
        if ($note->getOwnerUid() === $this->uid()) {
            $oldAssigned = $note->getAssignedUid();
            $assignedUid = $this->normalizeAssignment($assignedUid);
            $note->setAssignedUid($assignedUid);
            $note->setDueAt($dueAt);
            if ($assignedUid !== $oldAssigned) {
                $this->safeNotifyAssignment($note, $assignedUid);
            }
        }
        $note->setUpdatedAt(time());
        return new DataResponse($this->noteMapper->update($note));
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        try { $note = $this->noteMapper->findForUser($id, $this->uid()); } catch (DoesNotExistException) { return new DataResponse(['error'=>'Not found'], Http::STATUS_NOT_FOUND); }
        if ($note->getOwnerUid() !== $this->uid()) return new DataResponse(['error'=>'Only owner can delete'], Http::STATUS_FORBIDDEN);
        foreach ($this->shareMapper->findByNote($id) as $share) $this->shareMapper->delete($share);
        $this->noteMapper->delete($note);
        return new DataResponse(['ok'=>true]);
    }

    #[NoAdminRequired]
    public function complete(int $id): DataResponse {
        try { $note = $this->noteMapper->findForUser($id, $this->uid()); } catch (DoesNotExistException) { return new DataResponse(['error'=>'Not found'], Http::STATUS_NOT_FOUND); }
        $wasCompleted = $note->getCompletedAt() !== null;
        $note->setCompletedAt($wasCompleted ? null : time());
        $note->setUpdatedAt(time());
        $saved = $this->noteMapper->update($note);
        if ($note->getOwnerUid() !== $this->uid()) {
            $this->notifications->send(
                $saved,
                $note->getOwnerUid(),
                $wasCompleted ? NotificationService::EVENT_TASK_REOPENED : NotificationService::EVENT_TASK_COMPLETED,
                $this->uid()
            );
        }
        return new DataResponse($saved);
    }

    #[NoAdminRequired]
    public function share(int $id, string $shareType, string $shareWith, string $permission = 'view'): DataResponse {
        try { $note = $this->noteMapper->findForUser($id, $this->uid()); } catch (DoesNotExistException) { return new DataResponse(['error'=>'Not found'], Http::STATUS_NOT_FOUND); }
        if ($note->getOwnerUid() !== $this->uid()) return new DataResponse(['error'=>'Only owner can share'], Http::STATUS_FORBIDDEN);
        if (!in_array($shareType, ['user','group'], true)) return new DataResponse(['error'=>'Invalid share type'], Http::STATUS_BAD_REQUEST);
        $share = new Share();
        $share->setNoteId($id); $share->setShareType($shareType); $share->setShareWith($shareWith);
        $share->setPermission($permission === 'edit' ? 'edit' : 'view'); $share->setCreatedAt(time());
        $saved = $this->shareMapper->insert($share);
        if ($shareType === 'user' && $shareWith !== $this->uid()) $this->notifications->send($note, $shareWith, NotificationService::EVENT_SHARED, $this->uid());
        return new DataResponse($saved, Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    public function unshare(int $id, int $shareId): DataResponse {
        try { $note = $this->noteMapper->findForUser($id, $this->uid()); $share = $this->shareMapper->findOne($shareId, $id); } catch (DoesNotExistException) { return new DataResponse(['error'=>'Not found'], Http::STATUS_NOT_FOUND); }
        if ($note->getOwnerUid() !== $this->uid()) return new DataResponse(['error'=>'Only owner can unshare'], Http::STATUS_FORBIDDEN);
        $this->shareMapper->delete($share); return new DataResponse(['ok'=>true]);
    }

}
