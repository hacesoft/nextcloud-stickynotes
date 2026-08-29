<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Service;

use OCA\StickyNotes\AppInfo\Application;
use OCA\StickyNotes\Db\Note;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IURLGenerator;
use OCP\Notification\IManager as NotificationManager;
use OCP\Security\ICrypto;
use Psr\Log\LoggerInterface;

class NotificationService {
    public const EVENT_ASSIGNED_USER = 'assigned_user';
    public const EVENT_ASSIGNED_GROUP = 'assigned_group';
    public const EVENT_SHARED = 'shared';
    public const EVENT_TASK_COMPLETED = 'task_completed';
    public const EVENT_TASK_REOPENED = 'task_reopened';
    public const EVENT_DUE_24H = 'due_24h';
    public const EVENT_DUE_1H = 'due_1h';
    public const EVENT_DUE_NOW = 'due_now';

    public function __construct(
        private IConfig $config,
        private NotificationManager $notificationManager,
        private IClientService $clientService,
        private IURLGenerator $url,
        private ICrypto $crypto,
        private IGroupManager $groupManager,
        private LoggerInterface $logger,
    ) {}

    public function defaultEvents(): array {
        return [
            self::EVENT_ASSIGNED_USER => true,
            self::EVENT_ASSIGNED_GROUP => true,
            self::EVENT_SHARED => true,
            self::EVENT_TASK_COMPLETED => true,
            self::EVENT_TASK_REOPENED => true,
            self::EVENT_DUE_24H => true,
            self::EVENT_DUE_1H => true,
            self::EVENT_DUE_NOW => true,
        ];
    }

    public function getPreferences(string $uid, bool $includeToken = false): array {
        $events = $this->defaultEvents();
        $stored = json_decode($this->config->getUserValue($uid, Application::APP_ID, 'notify_events', '{}'), true);
        if (is_array($stored)) {
            foreach ($events as $key => $default) {
                if (array_key_exists($key, $stored)) $events[$key] = (bool)$stored[$key];
            }
        }
        $token = '';
        if ($includeToken) {
            $encrypted = $this->config->getUserValue($uid, Application::APP_ID, 'ntfy_token', '');
            if ($encrypted !== '') {
                try { $token = $this->crypto->decrypt($encrypted); } catch (\Throwable) { $token = ''; }
            }
        }
        return [
            'nextcloudEnabled' => $this->config->getUserValue($uid, Application::APP_ID, 'notify_nextcloud', '1') === '1',
            'ntfyEnabled' => $this->config->getUserValue($uid, Application::APP_ID, 'notify_ntfy', '0') === '1',
            'ntfyServer' => $this->config->getUserValue($uid, Application::APP_ID, 'ntfy_server', 'https://ntfy.sh'),
            'ntfyTopic' => $this->config->getUserValue($uid, Application::APP_ID, 'ntfy_topic', ''),
            'ntfyToken' => $token,
            'events' => $events,
        ];
    }

    public function savePreferences(string $uid, bool $nextcloudEnabled, bool $ntfyEnabled, string $ntfyServer, string $ntfyTopic, ?string $ntfyToken, array $events): array {
        $ntfyServer = rtrim(trim($ntfyServer), '/');
        if ($ntfyServer === '') $ntfyServer = 'https://ntfy.sh';
        if (!preg_match('#^https?://#i', $ntfyServer)) throw new \InvalidArgumentException('ntfy server must use http:// or https://');
        $ntfyTopic = trim($ntfyTopic);
        $cleanEvents = $this->defaultEvents();
        foreach ($cleanEvents as $key => $default) $cleanEvents[$key] = array_key_exists($key, $events) ? (bool)$events[$key] : $default;
        $this->config->setUserValue($uid, Application::APP_ID, 'notify_nextcloud', $nextcloudEnabled ? '1' : '0');
        $this->config->setUserValue($uid, Application::APP_ID, 'notify_ntfy', $ntfyEnabled ? '1' : '0');
        $this->config->setUserValue($uid, Application::APP_ID, 'ntfy_server', $ntfyServer);
        $this->config->setUserValue($uid, Application::APP_ID, 'ntfy_topic', $ntfyTopic);
        $this->config->setUserValue($uid, Application::APP_ID, 'notify_events', json_encode($cleanEvents, JSON_THROW_ON_ERROR));
        if ($ntfyToken !== null) {
            $ntfyToken = trim($ntfyToken);
            $this->config->setUserValue($uid, Application::APP_ID, 'ntfy_token', $ntfyToken === '' ? '' : $this->crypto->encrypt($ntfyToken));
        }
        return $this->getPreferences($uid, false);
    }

    public function recipientsForAssignment(Note $note): array {
        $target = $note->getAssignedUid();
        if ($target === null || $target === '') return [];
        if (str_starts_with($target, 'group:')) {
            $group = $this->groupManager->get(substr($target, 6));
            if ($group === null) return [];
            return array_values(array_map(static fn($u) => $u->getUID(), $group->getUsers()));
        }
        return [$target];
    }

    public function recipientsForDue(Note $note): array {
        $recipients = $this->recipientsForAssignment($note);
        return $recipients === [] ? [$note->getOwnerUid()] : $recipients;
    }

    public function send(Note $note, string $uid, string $event, string $actorUid = ''): bool {
        $prefs = $this->getPreferences($uid, true);
        if (!(bool)($prefs['events'][$event] ?? false)) return false;
        if (!$prefs['nextcloudEnabled'] && !$prefs['ntfyEnabled']) return false;
        [$subject, $params, $ntfyTitle, $ntfyBody, $tags, $priority] = $this->message($note, $event, $actorUid);
        $sent = false;
        if ($prefs['nextcloudEnabled']) {
            try {
                $n = $this->notificationManager->createNotification();
                $link = $this->noteLink($note);
                $openAction = $n->createAction();
                $openAction->setLabel('open_note')->setLink($link, 'GET');
                $n->setApp(Application::APP_ID)
                    ->setUser($uid)
                    ->setDateTime(new \DateTime())
                    ->setObject('note', (string)$note->getId())
                    ->setSubject($subject, $params)
                    ->setLink($link)
                    ->addAction($openAction);
                $this->notificationManager->notify($n);
                $sent = true;
            } catch (\Throwable $e) {
                $this->logger->warning('Sticky Notes Nextcloud notification failed', ['exception' => $e, 'uid' => $uid, 'event' => $event]);
            }
        }
        if ($prefs['ntfyEnabled'] && $prefs['ntfyTopic'] !== '') {
            try {
                $this->sendNtfy($prefs, $ntfyTitle, $ntfyBody, $tags, $priority, $this->noteLink($note));
                $sent = true;
            } catch (\Throwable $e) {
                $this->logger->warning('Sticky Notes ntfy notification failed', ['exception' => $e, 'uid' => $uid, 'event' => $event]);
            }
        }
        return $sent;
    }

    public function testNtfy(string $uid): void {
        $prefs = $this->getPreferences($uid, true);
        if ($prefs['ntfyTopic'] === '') throw new \RuntimeException('ntfy topic is empty');
        $this->sendNtfy($prefs, 'Sticky Notes', 'Test notification from Sticky Notes.', ['white_check_mark'], 3, $this->url->linkToRouteAbsolute('stickynotes.page.index'));
    }

    public function markDueSent(string $uid, int $noteId, string $event, int $dueAt): void {
        $this->config->setUserValue($uid, Application::APP_ID, sprintf('due_%d_%s', $noteId, $event), (string)$dueAt);
    }

    public function wasDueSent(string $uid, int $noteId, string $event, int $dueAt): bool {
        return $this->config->getUserValue($uid, Application::APP_ID, sprintf('due_%d_%s', $noteId, $event), '') === (string)$dueAt;
    }

    private function sendNtfy(array $prefs, string $title, string $body, array $tags, int $priority, string $clickUrl): void {
        $server = rtrim((string)$prefs['ntfyServer'], '/');
        $topic = rawurlencode((string)$prefs['ntfyTopic']);
        $headers = ['Title' => $title, 'Priority' => (string)$priority, 'Tags' => implode(',', $tags), 'Click' => $clickUrl, 'Content-Type' => 'text/plain; charset=utf-8'];
        if (($prefs['ntfyToken'] ?? '') !== '') $headers['Authorization'] = 'Bearer ' . $prefs['ntfyToken'];
        $this->clientService->newClient()->post($server . '/' . $topic, ['headers' => $headers, 'body' => $body, 'timeout' => 10]);
    }

    private function noteLink(Note $note): string {
        return $this->url->linkToRouteAbsolute('stickynotes.page.index') . '?note=' . rawurlencode((string)$note->getId());
    }

    private function message(Note $note, string $event, string $actorUid): array {
        $title = trim($note->getTitle()) !== '' ? $note->getTitle() : 'Untitled note';
        return match ($event) {
            self::EVENT_ASSIGNED_USER => ['assigned', [$title, $actorUid], 'Sticky Notes – assigned', 'You were assigned: ' . $title, ['pushpin'], 4],
            self::EVENT_ASSIGNED_GROUP => ['assigned_group', [$title, $actorUid], 'Sticky Notes – group task', 'A task was assigned to your group: ' . $title, ['busts_in_silhouette'], 4],
            self::EVENT_SHARED => ['shared', [$title, $actorUid], 'Sticky Notes – shared', 'A sticky note was shared with you: ' . $title, ['link'], 3],
            self::EVENT_TASK_COMPLETED => ['completed', [$title, $actorUid], 'Sticky Notes – completed', 'Completed: ' . $title, ['white_check_mark'], 3],
            self::EVENT_TASK_REOPENED => ['reopened', [$title, $actorUid], 'Sticky Notes – reopened', 'Reopened: ' . $title, ['arrows_counterclockwise'], 3],
            self::EVENT_DUE_24H => ['due_24h', [$title], 'Sticky Notes – due soon', 'Due within 24 hours: ' . $title, ['clock1'], 3],
            self::EVENT_DUE_1H => ['due_1h', [$title], 'Sticky Notes – due soon', 'Due within 1 hour: ' . $title, ['alarm_clock'], 4],
            self::EVENT_DUE_NOW => ['due_now', [$title], 'Sticky Notes – due now', 'Task is due now: ' . $title, ['rotating_light'], 5],
            default => throw new \InvalidArgumentException('Unknown notification event'),
        };
    }
}
