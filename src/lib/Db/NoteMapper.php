<?php

declare(strict_types=1);

namespace OCA\HcStickyNotes\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class NoteMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'hc_stickynotes_notes', Note::class);
    }

    public function findForUser(int $id, string $uid, array $groupIds = []): Note {
        $qb = $this->db->getQueryBuilder();
        $access = $qb->expr()->orX(
            $qb->expr()->eq('n.owner_uid', $qb->createNamedParameter($uid)),
            $qb->expr()->eq('n.assigned_uid', $qb->createNamedParameter($uid)),
            $qb->expr()->andX(
                $qb->expr()->eq('s.share_type', $qb->createNamedParameter('user')),
                $qb->expr()->eq('s.share_with', $qb->createNamedParameter($uid))
            )
        );
        if ($groupIds !== []) {
            $access->add($qb->expr()->andX(
                $qb->expr()->eq('s.share_type', $qb->createNamedParameter('group')),
                $qb->expr()->in('s.share_with', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY))
            ));
            $access->add($qb->expr()->in('n.assigned_uid', $qb->createNamedParameter(
                array_map(static fn (string $gid): string => 'group:' . $gid, $groupIds),
                IQueryBuilder::PARAM_STR_ARRAY
            )));
        }
        $qb->selectDistinct('n.*')
            ->from('hc_stickynotes_notes', 'n')
            ->leftJoin('n', 'hc_stickynotes_shares', 's', $qb->expr()->eq('s.note_id', 'n.id'))
            ->where($qb->expr()->eq('n.id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->andWhere($access);
        return $this->findEntity($qb);
    }

    public function canEdit(Note $note, string $uid, array $groupIds = []): bool {
        if ($note->getOwnerUid() === $uid || $note->getAssignedUid() === $uid
            || in_array($note->getAssignedUid(), array_map(static fn (string $gid): string => 'group:' . $gid, $groupIds), true)) {
            return true;
        }
        $qb = $this->db->getQueryBuilder();
        $access = $qb->expr()->orX($qb->expr()->andX(
            $qb->expr()->eq('share_type', $qb->createNamedParameter('user')),
            $qb->expr()->eq('share_with', $qb->createNamedParameter($uid))
        ));
        if ($groupIds !== []) {
            $access->add($qb->expr()->andX(
                $qb->expr()->eq('share_type', $qb->createNamedParameter('group')),
                $qb->expr()->in('share_with', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY))
            ));
        }
        $qb->select('id')->from('hc_stickynotes_shares')
            ->where($qb->expr()->eq('note_id', $qb->createNamedParameter((int)$note->getId(), IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->in('permission', $qb->createNamedParameter(['edit', 'assign'], IQueryBuilder::PARAM_STR_ARRAY)))
            ->andWhere($access)->setMaxResults(1);
        return $qb->executeQuery()->fetchOne() !== false;
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
            ->from('hc_stickynotes_notes', 'n')
            ->leftJoin('n', 'hc_stickynotes_shares', 's', $qb->expr()->eq('s.note_id', 'n.id'))
            ->where($or)
            ->orderBy('n.updated_at', 'DESC');
        return $this->findEntities($qb);
    }

    public function findPendingDueBefore(int $timestamp): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('hc_stickynotes_notes')
            ->where($qb->expr()->eq('type', $qb->createNamedParameter('task')))
            ->andWhere($qb->expr()->isNull('completed_at'))
            ->andWhere($qb->expr()->isNotNull('due_at'))
            ->andWhere($qb->expr()->lte('due_at', $qb->createNamedParameter($timestamp, IQueryBuilder::PARAM_INT)))
            ->orderBy('due_at', 'ASC');
        return $this->findEntities($qb);
    }
}
