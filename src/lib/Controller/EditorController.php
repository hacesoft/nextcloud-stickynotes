<?php

declare(strict_types=1);

namespace OCA\HcStickyNotes\Controller;

use OCA\HcStickyNotes\AppInfo\Application;
use OCA\HcStickyNotes\Db\NoteMapper;
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
        $document = new \DOMDocument('1.0', 'UTF-8');
        // Rebuild allowed elements and attributes; regex over HTML misses unquoted URLs.
        if (!@$document->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        $body = $document->getElementsByTagName('body')->item(0);
        $render = static function (\DOMNode $node) use (&$render): string {
            if ($node instanceof \DOMText) {
                return htmlspecialchars($node->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            if (!$node instanceof \DOMElement) return '';
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'svg', 'math', 'iframe', 'object', 'form'], true)) return '';
            $inside = '';
            foreach ($node->childNodes as $child) $inside .= $render($child);
            if (!in_array($tag, ['p','br','strong','b','em','i','u','s','h1','h2','h3','h4','ul','ol','li','a','blockquote','code','pre','table','thead','tbody','tr','th','td'], true)) return $inside;
            if ($tag === 'br') return '<br>';
            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
                if (!in_array($scheme, ['http', 'https', 'mailto'], true)
                    || preg_match('/[\x00-\x20]/', $href)
                    || ($scheme !== 'mailto' && !parse_url($href, PHP_URL_HOST))) {
                    return $inside;
                }
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" target="_blank" rel="noreferrer noopener">' . $inside . '</a>';
            }
            return '<' . $tag . '>' . $inside . '</' . $tag . '>';
        };
        $clean = '';
        if ($body) foreach ($body->childNodes as $child) $clean .= $render($child);
        return trim($clean);
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
            'titleHtml' => $this->sanitize((string)($data['titleHtml'] ?? '')),
            'bodyHtml' => $this->sanitize((string)($data['bodyHtml'] ?? '')),
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
            $note = $this->noteMapper->findForUser($id, $this->uid(), $this->groupManager->getUserGroupIds($this->userSession->getUser()));
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        if (!$this->noteMapper->canEdit($note, $this->uid(), $this->groupManager->getUserGroupIds($this->userSession->getUser()))) {
            return new DataResponse(['error' => 'Edit permission required'], Http::STATUS_FORBIDDEN);
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
            $note = $this->noteMapper->findForUser($id, $this->uid(), $this->groupManager->getUserGroupIds($this->userSession->getUser()));
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        if ($note->getOwnerUid() !== $this->uid()) {
            return new DataResponse(['error' => 'Owner permission required'], Http::STATUS_FORBIDDEN);
        }
        $this->config->deleteUserValue(
            $note->getOwnerUid(),
            Application::APP_ID,
            $this->key($id)
        );

        return new DataResponse(['ok' => true]);
    }
}
