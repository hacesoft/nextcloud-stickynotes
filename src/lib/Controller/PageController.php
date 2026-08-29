<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
    public function __construct(IRequest $request) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        Util::addStyle(Application::APP_ID, 'style-1.1.1');
        Util::addScript(Application::APP_ID, 'app-1.1.1');
        return new TemplateResponse(
            Application::APP_ID,
            'main',
            ['version' => Application::VERSION]
        );
    }
}
