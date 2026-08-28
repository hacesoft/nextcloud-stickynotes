<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShareMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'stickynotes_shares', Share::class);
    }

    public function findByNote(int $noteId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('stickynotes_shares')
            ->where($qb->expr()->eq('note_id', $qb->createNamedParameter($noteId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }

    public function findOne(int $id, int $noteId): Share {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('stickynotes_shares')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('note_id', $qb->createNamedParameter($noteId, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }
}
