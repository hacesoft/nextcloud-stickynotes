<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCA\StickyNotes\Db\NoteMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

class EditorController extends Controller {
    public function __construct(
        IRequest $request,
        private NoteMapper $noteMapper,
        private IUserSession $userSession,
        private IGroupManager $groupManager,
        private IConfig $config,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    private function uid(): string {
        return $this->userSession->getUser()?->getUID() ?? '';
    }

    private function key(int $noteId): string {
        return 'note_editor_' . $noteId;
    }

    private function sanitize(string $html): string {
        $html = strip_tags(
            $html,
            '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><ul><ol><li><a><blockquote><code><pre><table><thead><tbody><tr><th><td>'
        );

        $html = preg_replace(
            '~\son\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~i',
            '',
            $html
        ) ?? '';

        $html = preg_replace(
            '~\sstyle\s*=\s*(?:"[^"]*"|\'[^\']*\')~i',
            '',
            $html
        ) ?? '';

        $html = preg_replace_callback(
            '~<a\b([^>]*)href\s*=\s*([\'"])(.*?)\2([^>]*)>~i',
            static function(array $m): string {
                $href = trim($m[3]);
                if (!preg_match('#^(https?://|mailto:)#i', $href)) {
                    return '<a>';
                }
                return '<a href="' .
                    htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
                    '" target="_blank" rel="noreferrer noopener">';
            },
            $html
        ) ?? '';

        return trim($html);
    }

    private function readForOwner(string $ownerUid, int $noteId): array {
        $raw = $this->config->getUserValue(
            $ownerUid,
            Application::APP_ID,
            $this->key($noteId),
            ''
        );

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['titleHtml' => '', 'bodyHtml' => ''];
        }

        return [
            'titleHtml' => (string)($data['titleHtml'] ?? ''),
            'bodyHtml' => (string)($data['bodyHtml'] ?? ''),
        ];
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $user = $this->userSession->getUser();
        $groups = $user ? $this->groupManager->getUserGroupIds($user) : [];
        $notes = $this->noteMapper->findAllForUser($this->uid(), $groups);

        $result = [];
        foreach ($notes as $note) {
            $result[(string)$note->getId()] = $this->readForOwner(
                $note->getOwnerUid(),
                (int)$note->getId()
            );
        }

        return new DataResponse($result);
    }

    #[NoAdminRequired]
    public function save(int $id, array $editor = []): DataResponse {
        try {
            $note = $this->noteMapper->findForUser($id, $this->uid());
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        // Store formatting under the note owner's config so all viewers see the same formatting.
        $clean = [
            'titleHtml' => $this->sanitize((string)($editor['titleHtml'] ?? '')),
            'bodyHtml' => $this->sanitize((string)($editor['bodyHtml'] ?? '')),
        ];

        $this->config->setUserValue(
            $note->getOwnerUid(),
            Application::APP_ID,
            $this->key($id),
            json_encode($clean, JSON_THROW_ON_ERROR)
        );

        return new DataResponse(['ok' => true, 'editor' => $clean]);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        try {
            $note = $this->noteMapper->findForUser($id, $this->uid());
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        $this->config->deleteUserValue(
            $note->getOwnerUid(),
            Application::APP_ID,
            $this->key($id)
        );

        return new DataResponse(['ok' => true]);
    }
}
