<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Db;

use OCP\AppFramework\Db\Entity;

class Share extends Entity implements \JsonSerializable {
    protected int $noteId = 0;
    protected string $shareType = 'user';
    protected string $shareWith = '';
    protected string $permission = 'view';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('noteId', 'integer');
        $this->addType('createdAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'noteId' => $this->getNoteId(),
            'shareType' => $this->getShareType(),
            'shareWith' => $this->getShareWith(),
            'permission' => $this->getPermission(),
            'createdAt' => $this->getCreatedAt(),
        ];
    }
}
