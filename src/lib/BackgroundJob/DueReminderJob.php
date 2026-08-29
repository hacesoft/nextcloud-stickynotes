<?php

declare(strict_types=1);

namespace OCA\StickyNotes\BackgroundJob;

use OCA\StickyNotes\Db\NoteMapper;
use OCA\StickyNotes\Service\NotificationService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

class DueReminderJob extends TimedJob {
    public function __construct(
        ITimeFactory $time,
        private NoteMapper $noteMapper,
        private NotificationService $notifications,
        private LoggerInterface $logger,
    ) {
        parent::__construct($time);
        $this->setInterval(300);
        $this->setAllowParallelRuns(false);
    }

    protected function run($argument): void {
        $now = time();
        try {
            foreach ($this->noteMapper->findPendingDueBefore($now + 86400) as $note) {
                $dueAt = (int)$note->getDueAt();
                if ($dueAt <= 0) continue;
                $seconds = $dueAt - $now;
                $event = $seconds <= 0 ? NotificationService::EVENT_DUE_NOW : ($seconds <= 3600 ? NotificationService::EVENT_DUE_1H : NotificationService::EVENT_DUE_24H);
                foreach ($this->notifications->recipientsForDue($note) as $uid) {
                    if ($this->notifications->wasDueSent($uid, (int)$note->getId(), $event, $dueAt)) continue;
                    if ($this->notifications->send($note, $uid, $event)) $this->notifications->markDueSent($uid, (int)$note->getId(), $event, $dueAt);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Sticky Notes due reminder job failed', ['exception' => $e]);
        }
    }
}
