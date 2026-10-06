<?php

declare(strict_types=1);

require_once '/var/www/html/lib/base.php';
$db = \OC::$server->get(\OCP\IDBConnection::class);
if (!$db->tableExists('jobs')) exit(0);
$qb = $db->getQueryBuilder();
$count = $qb->delete('jobs')
    ->where($qb->expr()->eq('class', $qb->createNamedParameter('OCA\\HcStickyNotes\\BackgroundJob\\DueReminderJob')))
    ->executeStatement();
fwrite(STDOUT, "Removed {$count} Sticky Notes background job(s).\n");
