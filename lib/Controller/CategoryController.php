<?php
declare(strict_types=1);

namespace OCA\StickyNotes\Controller;

use OCA\StickyNotes\AppInfo\Application;
use OCA\StickyNotes\Db\Category;
use OCA\StickyNotes\Db\CategoryMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IUserSession;

class CategoryController extends Controller {
    public function __construct(
        IRequest $request,
        private CategoryMapper $mapper,
        private IUserSession $session,
        private IGroupManager $groupManager,
        private IConfig $config,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    private function uid(): string {
        return $this->session->getUser()?->getUID() ?? '';
    }

    private function isAdmin(): bool {
        return $this->groupManager->isAdmin($this->uid());
    }

    private function validColor(string $color): string {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : '#4f86f7';
    }


    private function validMode(string $mode): string {
        return in_array($mode, ['inherit','header','left','right','top','bottom','border','corner','badge','full'], true) ? $mode : 'inherit';
    }

    private function styleKey(int $id): string {
        return 'category_style_' . $id;
    }

    private function categoryStyle(Category $category): array {
        $fallback = ['markerMode'=>'inherit','background'=>'#fff59d','markerColor'=>$category->getColor()];
        $raw = $category->getIsSystem()
            ? $this->config->getAppValue(Application::APP_ID, $this->styleKey((int)$category->getId()), '')
            : $this->config->getUserValue($category->getOwnerUid() ?? $this->uid(), Application::APP_ID, $this->styleKey((int)$category->getId()), '');
        if ($raw === '') return $fallback;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? array_merge($fallback, $decoded) : $fallback;
    }

    private function saveCategoryStyle(Category $category, string $markerMode, string $background, string $markerColor): void {
        $style = json_encode([
            'markerMode'=>$this->validMode($markerMode),
            'background'=>$this->validColor($background),
            'markerColor'=>$this->validColor($markerColor),
        ]);
        if ($category->getIsSystem()) {
            $this->config->setAppValue(Application::APP_ID, $this->styleKey((int)$category->getId()), $style);
        } else {
            $this->config->setUserValue($category->getOwnerUid() ?? $this->uid(), Application::APP_ID, $this->styleKey((int)$category->getId()), $style);
        }
    }

    private function deleteCategoryStyle(Category $category): void {
        if ($category->getIsSystem()) $this->config->deleteAppValue(Application::APP_ID, $this->styleKey((int)$category->getId()));
        else $this->config->deleteUserValue($category->getOwnerUid() ?? $this->uid(), Application::APP_ID, $this->styleKey((int)$category->getId()));
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $result = [];
        foreach ($this->mapper->findVisible($this->uid()) as $category) {
            $row = $category->jsonSerialize();
            $row['style'] = $this->categoryStyle($category);
            $row['canEdit'] = $category->getIsSystem()
                ? $this->isAdmin()
                : $category->getOwnerUid() === $this->uid();
            $result[] = $row;
        }
        return new DataResponse(['categories' => $result, 'isAdmin' => $this->isAdmin()]);
    }

    #[NoAdminRequired]
    public function create(string $name, string $color = '#4f86f7', string $icon = '', bool $isSystem = false, string $markerMode = 'inherit', string $background = '#fff59d', string $markerColor = '#4f86f7'): DataResponse {
        $name = mb_substr(trim($name), 0, 100);
        if ($name === '') {
            return new DataResponse(['error' => 'Category name is required'], Http::STATUS_BAD_REQUEST);
        }
        if ($isSystem && !$this->isAdmin()) {
            return new DataResponse(['error' => 'Admin required'], Http::STATUS_FORBIDDEN);
        }

        $category = new Category();
        $category->setOwnerUid($isSystem ? null : $this->uid());
        $category->setName($name);
        $category->setColor($this->validColor($color));
        $category->setIcon(mb_substr($icon, 0, 32));
        $category->setIsSystem($isSystem);
        $category->setCreatedAt(time());
        $category->setUpdatedAt(time());

        $category = $this->mapper->insert($category);
        $this->saveCategoryStyle($category, $markerMode, $background, $markerColor);
        $row = $category->jsonSerialize();
        $row['style'] = $this->categoryStyle($category);
        return new DataResponse($row, Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    public function update(int $id, string $name, string $color, string $icon = '', string $markerMode = 'inherit', string $background = '#fff59d', string $markerColor = '#4f86f7'): DataResponse {
        try {
            $category = $this->mapper->findOne($id);
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        if (($category->getIsSystem() && !$this->isAdmin())
            || (!$category->getIsSystem() && $category->getOwnerUid() !== $this->uid())) {
            return new DataResponse(['error' => 'Forbidden'], Http::STATUS_FORBIDDEN);
        }

        $name = mb_substr(trim($name), 0, 100);
        if ($name === '') {
            return new DataResponse(['error' => 'Category name is required'], Http::STATUS_BAD_REQUEST);
        }

        $category->setName($name);
        $category->setColor($this->validColor($color));
        $category->setIcon(mb_substr($icon, 0, 32));
        $category->setUpdatedAt(time());

        $category = $this->mapper->update($category);
        $this->saveCategoryStyle($category, $markerMode, $background, $markerColor);
        $row = $category->jsonSerialize();
        $row['style'] = $this->categoryStyle($category);
        return new DataResponse($row);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        try {
            $category = $this->mapper->findOne($id);
        } catch (DoesNotExistException) {
            return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        if (($category->getIsSystem() && !$this->isAdmin())
            || (!$category->getIsSystem() && $category->getOwnerUid() !== $this->uid())) {
            return new DataResponse(['error' => 'Forbidden'], Http::STATUS_FORBIDDEN);
        }

        $this->deleteCategoryStyle($category);
        $this->mapper->delete($category);
        return new DataResponse(['ok' => true]);
    }
}
