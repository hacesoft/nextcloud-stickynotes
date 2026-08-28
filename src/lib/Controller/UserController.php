<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;

class UserController extends Controller {
    public function __construct(
        IRequest $request,
        private IUserManager $userManager,
        private IGroupManager $groupManager,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function search(string $q = ''): DataResponse {
        $q = trim($q);

        $users = [];
        // IUserManager::search() works with an empty pattern and therefore
        // gives us a useful initial list when the picker is opened.
        foreach ($this->userManager->search($q, 40, 0) as $user) {
            $users[] = [
                'id' => $user->getUID(),
                'displayName' => $user->getDisplayName(),
            ];
        }

        $groups = [];
        foreach ($this->groupManager->search($q, 40) as $group) {
            $groups[] = [
                'id' => $group->getGID(),
                'displayName' => $group->getDisplayName(),
            ];
        }

        return new DataResponse([
            'users' => $users,
            'groups' => $groups,
        ]);
    }
}
