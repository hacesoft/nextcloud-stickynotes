<?php
declare(strict_types=1);
namespace OCA\StickyNotes\Db;
use OCP\AppFramework\Db\Entity;
class Category extends Entity implements \JsonSerializable {
 protected ?string $ownerUid=null; protected string $name=''; protected string $color='#4f86f7'; protected string $icon=''; protected bool $isSystem=false; protected int $createdAt=0; protected int $updatedAt=0;
 public function __construct(){ $this->addType('id','integer');$this->addType('isSystem','boolean');$this->addType('createdAt','integer');$this->addType('updatedAt','integer');}
 public function jsonSerialize():array{return ['id'=>$this->getId(),'ownerUid'=>$this->getOwnerUid(),'name'=>$this->getName(),'color'=>$this->getColor(),'icon'=>$this->getIcon(),'isSystem'=>$this->getIsSystem(),'createdAt'=>$this->getCreatedAt(),'updatedAt'=>$this->getUpdatedAt()];}
}