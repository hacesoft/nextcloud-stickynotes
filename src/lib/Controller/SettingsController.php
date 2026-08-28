<?php
declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {
    public function __construct(
        IRequest $request,
        private IConfig $config,
        private IUserSession $session,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    private function uid(): string {
        return $this->session->getUser()?->getUID() ?? '';
    }

    private function jsonArray(string $key): array {
        $raw = $this->config->getUserValue($this->uid(), Application::APP_ID, $key, '[]');
        $value = json_decode($raw, true);
        return is_array($value) ? array_values($value) : [];
    }

    #[NoAdminRequired]
    public function get(): DataResponse {
        $uid = $this->uid();
        return new DataResponse([
            'baseColor' => $this->config->getUserValue($uid, Application::APP_ID, 'base_color', '#fff59d'),
            'markerMode' => $this->config->getUserValue($uid, Application::APP_ID, 'marker_mode', 'header'),
            'markerSize' => (int)$this->config->getUserValue($uid, Application::APP_ID, 'marker_size', '7'),
            'pinnedNoteIds' => array_map('intval', $this->jsonArray('pinned_note_ids')),
            'noteOrder' => array_map('intval', $this->jsonArray('note_order')),
            'sortMode' => $this->config->getUserValue($uid, Application::APP_ID, 'sort_mode', 'manual'),
            'widgetColumns' => (int)$this->config->getUserValue($uid, Application::APP_ID, 'widget_columns', '2'),
            'widgetRows' => (int)$this->config->getUserValue($uid, Application::APP_ID, 'widget_rows', '4'),
            'pageSize' => (int)$this->config->getUserValue($uid, Application::APP_ID, 'page_size', '24'),
            'layoutWidth' => $this->config->getUserValue($uid, Application::APP_ID, 'layout_width', 'full'),
            'notificationMode' => $this->config->getUserValue($uid, Application::APP_ID, 'notification_mode', 'all'),
            'helpMode' => $this->config->getUserValue($uid, Application::APP_ID, 'help_mode', '0') === '1',
            'randomTilt' => $this->config->getUserValue($uid, Application::APP_ID, 'random_tilt', '1') === '1',
            'noteShadow' => $this->config->getUserValue($uid, Application::APP_ID, 'note_shadow', '1') === '1',
        ]);
    }

    #[NoAdminRequired]
    public function save(string $baseColor = '#fff59d', string $markerMode = 'header', int $markerSize = 7, string $sortMode = 'manual', int $widgetColumns = 2, int $widgetRows = 4, int $pageSize = 24, string $layoutWidth = 'full', string $notificationMode = 'all', bool $helpMode = false, bool $randomTilt = true, bool $noteShadow = true): DataResponse {
        $modes = ['full','header','left','corner','border','badge'];
        if (!in_array($markerMode, $modes, true)) $markerMode = 'header';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $baseColor)) $baseColor = '#fff59d';
        $markerSize = max(2, min(16, $markerSize));
        $sortModes = ['manual','category','newest','oldest'];
        if (!in_array($sortMode, $sortModes, true)) $sortMode = 'manual';
        $uid = $this->uid();
        $this->config->setUserValue($uid, Application::APP_ID, 'base_color', strtolower($baseColor));
        $this->config->setUserValue($uid, Application::APP_ID, 'marker_mode', $markerMode);
        $this->config->setUserValue($uid, Application::APP_ID, 'marker_size', (string)$markerSize);
        $this->config->setUserValue($uid, Application::APP_ID, 'sort_mode', $sortMode);
        $widgetColumns = max(1, min(4, $widgetColumns));
        $widgetRows = max(1, min(6, $widgetRows));
        $allowedPageSizes = [12,24,36,48,72];
        if (!in_array($pageSize, $allowedPageSizes, true)) $pageSize = 24;
        $this->config->setUserValue($uid, Application::APP_ID, 'widget_columns', (string)$widgetColumns);
        $this->config->setUserValue($uid, Application::APP_ID, 'widget_rows', (string)$widgetRows);
        $this->config->setUserValue($uid, Application::APP_ID, 'page_size', (string)$pageSize);
        $layoutWidth = in_array($layoutWidth, ['full','centered'], true) ? $layoutWidth : 'full';
        $notificationMode = in_array($notificationMode, ['all','individual','group','none'], true) ? $notificationMode : 'all';
        $this->config->setUserValue($uid, Application::APP_ID, 'layout_width', $layoutWidth);
        $this->config->setUserValue($uid, Application::APP_ID, 'notification_mode', $notificationMode);
        $this->config->setUserValue($uid, Application::APP_ID, 'help_mode', $helpMode ? '1' : '0');
        $this->config->setUserValue($uid, Application::APP_ID, 'random_tilt', $randomTilt ? '1' : '0');
        $this->config->setUserValue($uid, Application::APP_ID, 'note_shadow', $noteShadow ? '1' : '0');
        return $this->get();
    }

    #[NoAdminRequired]
    public function saveLayout(array $pinnedNoteIds = [], array $noteOrder = []): DataResponse {
        $pinned = array_values(array_unique(array_map('intval', $pinnedNoteIds)));
        $order = array_values(array_unique(array_map('intval', $noteOrder)));
        $this->config->setUserValue($this->uid(), Application::APP_ID, 'pinned_note_ids', json_encode($pinned, JSON_THROW_ON_ERROR));
        $this->config->setUserValue($this->uid(), Application::APP_ID, 'note_order', json_encode($order, JSON_THROW_ON_ERROR));
        return $this->get();
    }
}
