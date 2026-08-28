<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Db;

use OCP\AppFramework\Db\Entity;

class Note extends Entity implements \JsonSerializable {
    protected string $ownerUid = '';
    protected string $title = '';
    protected string $content = '';
    protected string $color = '#4f86f7';
    protected ?int $categoryId = null;
    protected string $type = 'note';
    protected string $priority = 'normal';
    protected ?string $assignedUid = null;
    protected ?int $dueAt = null;
    protected ?int $completedAt = null;
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('categoryId', 'integer');
        $this->addType('dueAt', 'integer');
        $this->addType('completedAt', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'ownerUid' => $this->getOwnerUid(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'color' => $this->getColor(),
            'categoryId' => $this->getCategoryId(),
            'type' => $this->getType(),
            'priority' => $this->getPriority(),
            'assignedUid' => $this->getAssignedUid(),
            'dueAt' => $this->getDueAt(),
            'completedAt' => $this->getCompletedAt(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }
}
