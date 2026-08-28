<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Notification;

use OCA\StickyNotes\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

class Notifier implements INotifier {
    public function __construct(private IFactory $factory, private IURLGenerator $url) {}
    public function getID(): string { return Application::APP_ID; }
    public function getName(): string { return $this->factory->get(Application::APP_ID)->t('Sticky Notes'); }

    public function prepare(INotification $notification, string $languageCode): INotification {
        if ($notification->getApp() !== Application::APP_ID) throw new UnknownNotificationException();
        $l = $this->factory->get(Application::APP_ID, $languageCode);
        $params = $notification->getSubjectParameters();
        $title = $params[0] ?? $l->t('Untitled note');
        $from = $params[1] ?? '';
        if ($notification->getSubject() === 'assigned') {
            $notification->setParsedSubject($l->t('%1$s assigned you a task: %2$s', [$from, $title]));
        } elseif ($notification->getSubject() === 'shared') {
            $notification->setParsedSubject($l->t('%1$s shared a sticky note with you: %2$s', [$from, $title]));
        } else {
            throw new UnknownNotificationException();
        }
        $notification->setLink($this->url->linkToRouteAbsolute('stickynotes.page.index'));
        return $notification;
    }
}
