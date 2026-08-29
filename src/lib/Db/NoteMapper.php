<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class NoteMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'stickynotes_notes', Note::class);
    }

    public function findForUser(int $id, string $uid): Note {
        $qb = $this->db->getQueryBuilder();
        $qb->select('n.*')
            ->from('stickynotes_notes', 'n')
            ->leftJoin('n', 'stickynotes_shares', 's', $qb->expr()->eq('s.note_id', 'n.id'))
            ->where($qb->expr()->eq('n.id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('n.owner_uid', $qb->createNamedParameter($uid)),
                $qb->expr()->eq('n.assigned_uid', $qb->createNamedParameter($uid)),
                $qb->expr()->andX(
                    $qb->expr()->eq('s.share_type', $qb->createNamedParameter('user')),
                    $qb->expr()->eq('s.share_with', $qb->createNamedParameter($uid))
                )
            ));
        return $this->findEntity($qb);
    }

    public function findAllForUser(string $uid, array $groupIds = []): array {
        $qb = $this->db->getQueryBuilder();
        $or = $qb->expr()->orX(
            $qb->expr()->eq('n.owner_uid', $qb->createNamedParameter($uid)),
            $qb->expr()->eq('n.assigned_uid', $qb->createNamedParameter($uid)),
            $qb->expr()->andX(
                $qb->expr()->eq('s.share_type', $qb->createNamedParameter('user')),
                $qb->expr()->eq('s.share_with', $qb->createNamedParameter($uid))
            )
        );
        if ($groupIds !== []) {
            $or->add($qb->expr()->andX(
                $qb->expr()->eq('s.share_type', $qb->createNamedParameter('group')),
                $qb->expr()->in('s.share_with', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY))
            ));
            $assignedGroups = array_map(static fn (string $gid): string => 'group:' . $gid, $groupIds);
            $or->add($qb->expr()->in(
                'n.assigned_uid',
                $qb->createNamedParameter($assignedGroups, IQueryBuilder::PARAM_STR_ARRAY)
            ));
        }
        $qb->selectDistinct('n.*')
            ->from('stickynotes_notes', 'n')
            ->leftJoin('n', 'stickynotes_shares', 's', $qb->expr()->eq('s.note_id', 'n.id'))
            ->where($or)
            ->orderBy('n.updated_at', 'DESC');
        return $this->findEntities($qb);
    }

    public function findPendingDueBefore(int $timestamp): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('stickynotes_notes')
            ->where($qb->expr()->eq('type', $qb->createNamedParameter('task')))
            ->andWhere($qb->expr()->isNull('completed_at'))
            ->andWhere($qb->expr()->isNotNull('due_at'))
            ->andWhere($qb->expr()->lte('due_at', $qb->createNamedParameter($timestamp, IQueryBuilder::PARAM_INT)))
            ->orderBy('due_at', 'ASC');
        return $this->findEntities($qb);
    }
}
