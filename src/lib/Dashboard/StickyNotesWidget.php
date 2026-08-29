<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Dashboard;

use OCA\StickyNotes\AppInfo\Application;
use OCP\Dashboard\IWidget;
use OCP\IURLGenerator;
use OCP\IL10N;
use OCP\Util;

class StickyNotesWidget implements IWidget {
    public function __construct(private IL10N $l10n, private IURLGenerator $url) {}
    public function getId(): string { return 'stickynotes'; }
    public function getTitle(): string { return $this->l10n->t('Sticky Notes'); }
    public function getOrder(): int { return 10; }
    public function getIconClass(): string { return 'icon-sticky-notes'; }
    public function getUrl(): ?string { return $this->url->linkToRouteAbsolute('stickynotes.page.index'); }
    public function load(): void {
        Util::addStyle(Application::APP_ID, 'style-1.1.1');
        Util::addScript(Application::APP_ID, 'dashboard-1.1.1');
    }
}
