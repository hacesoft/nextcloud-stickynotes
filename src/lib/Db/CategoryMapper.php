<?php
declare(strict_types=1);
namespace OCA\StickyNotes\Db;
use OCP\AppFramework\Db\QBMapper; use OCP\DB\QueryBuilder\IQueryBuilder; use OCP\IDBConnection;
class CategoryMapper extends QBMapper {
 public function __construct(IDBConnection $db){parent::__construct($db,'stickynotes_categories',Category::class);}
 public function findOne(int $id):Category{$q=$this->db->getQueryBuilder();$q->select('*')->from('stickynotes_categories')->where($q->expr()->eq('id',$q->createNamedParameter($id,IQueryBuilder::PARAM_INT)));return $this->findEntity($q);}
 public function findVisible(string $uid):array{$q=$this->db->getQueryBuilder();$q->select('*')->from('stickynotes_categories')->where($q->expr()->orX($q->expr()->eq('is_system',$q->createNamedParameter(true,IQueryBuilder::PARAM_BOOL)),$q->expr()->eq('owner_uid',$q->createNamedParameter($uid))))->orderBy('is_system','DESC')->addOrderBy('name','ASC');return $this->findEntities($q);}
}