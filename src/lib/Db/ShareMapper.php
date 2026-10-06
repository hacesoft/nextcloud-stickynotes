<?php

declare(strict_types=1);

namespace OCA\HcStickyNotes\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShareMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'hc_stickynotes_shares', Share::class);
    }

    public function findByNote(int $noteId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('hc_stickynotes_shares')
            ->where($qb->expr()->eq('note_id', $qb->createNamedParameter($noteId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }

    /** Assignment rows extend the original notes.assigned_uid field without changing old data. */
    public function assignmentTargets(int $noteId): array {
        $result = [];
        foreach ($this->findByNote($noteId) as $share) {
            if ($share->getPermission() === 'assign') {
                $result[] = $share->getShareType() === 'group' ? 'group:' . $share->getShareWith() : $share->getShareWith();
            }
        }
        return $result;
    }

    public function replaceExtraAssignments(int $noteId, array $targets): void {
        $existing = [];
        foreach ($this->findByNote($noteId) as $share) {
            if ($share->getPermission() !== 'assign') continue;
            $target = $share->getShareType() === 'group' ? 'group:' . $share->getShareWith() : $share->getShareWith();
            if (!in_array($target, $targets, true)) $this->delete($share);
            else $existing[$target] = true;
        }
        foreach ($targets as $target) {
            if (isset($existing[$target])) continue;
            $share = new Share();
            $share->setNoteId($noteId);
            $isGroup = str_starts_with($target, 'group:');
            $share->setShareType($isGroup ? 'group' : 'user');
            $share->setShareWith($isGroup ? substr($target, 6) : $target);
            $share->setPermission('assign');
            $share->setCreatedAt(time());
            $this->insert($share);
        }
    }

    public function findOne(int $id, int $noteId): Share {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('hc_stickynotes_shares')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('note_id', $qb->createNamedParameter($noteId, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }
}
