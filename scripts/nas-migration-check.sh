#!/bin/sh
set -eu
WEB_CONTAINER="${WEB_CONTAINER:-nextcloud-app}"
command -v docker >/dev/null 2>&1 || { echo 'ERROR: docker not found' >&2; exit 1; }
docker exec "$WEB_CONTAINER" test -f /var/www/html/lib/base.php || { echo "ERROR: Nextcloud not found in $WEB_CONTAINER" >&2; exit 1; }

docker exec -u www-data "$WEB_CONTAINER" php -r '
define("OC_CONSOLE", true);
require "/var/www/html/lib/base.php";
$db = \OC::$server->get(\OCP\IDBConnection::class);
$config = \OC::$server->get(\OCP\IConfig::class);
$prefix = (string)$config->getSystemValue("dbtableprefix", "oc_");
function tableExists($db, string $t): bool {
    try { $r=$db->executeQuery("SELECT 1 FROM `".$t."` LIMIT 1"); $r->closeCursor(); return true; }
    catch (\Throwable $e) { return false; }
}
function countRows($db, string $t) {
    return tableExists($db,$t) ? $db->executeQuery("SELECT COUNT(*) FROM `".$t."`")->fetchOne() : "MISSING";
}
function scalar($db, string $sql, array $params = []) {
    try { return $db->executeQuery($sql, $params)->fetchOne(); }
    catch (\Throwable $e) { return "ERR:" . $e->getMessage(); }
}
function missingIds($db, string $old, string $new) {
    if (!tableExists($db,$old)) return "N/A (legacy removed)";
    if (!tableExists($db,$new)) return "ERR:target missing";
    return scalar($db,"SELECT COUNT(*) FROM `".$old."` o LEFT JOIN `".$new."` n ON n.id=o.id WHERE n.id IS NULL");
}
echo "=== Sticky Notes DB migration check ===\n";
foreach (["stickynotes_notes","hc_stickynotes_notes","stickynotes_categories","hc_stickynotes_categories","stickynotes_shares","hc_stickynotes_shares","stickynotes_category_shares","hc_stickynotes_category_shares"] as $logical) {
    $physical=$prefix.$logical;
    echo str_pad($logical,36)." : ".countRows($db,$physical)."\n";
}
$prefs=$prefix."preferences"; $appconfig=$prefix."appconfig"; $notifs=$prefix."notifications"; $jobs=$prefix."jobs";
foreach (["stickynotes","hc_stickynotes"] as $app) {
    $pc = tableExists($db,$prefs) ? scalar($db,"SELECT COUNT(*) FROM `".$prefs."` WHERE appid = ?",[$app]) : "MISSING";
    $sc = tableExists($db,$appconfig) ? scalar($db,"SELECT COUNT(*) FROM `".$appconfig."` WHERE appid = ? AND configkey LIKE ?",[$app,"category_style_%"]) : "MISSING";
    echo str_pad("preferences[$app]",36)." : ".$pc."\n";
    echo str_pad("category styles[$app]",36)." : ".$sc."\n";
}
$oldNotif=tableExists($db,$notifs)?scalar($db,"SELECT COUNT(*) FROM `".$notifs."` WHERE app = ?",["stickynotes"]):"MISSING";
$newNotif=tableExists($db,$notifs)?scalar($db,"SELECT COUNT(*) FROM `".$notifs."` WHERE app = ?",["hc_stickynotes"]):"MISSING";
echo str_pad("notifications[stickynotes]",36)." : ".$oldNotif."\n";
echo str_pad("notifications[hc_stickynotes]",36)." : ".$newNotif."\n";
$oldJob=tableExists($db,$jobs)?scalar($db,"SELECT COUNT(*) FROM `".$jobs."` WHERE class = ?",["OCA\\StickyNotes\\BackgroundJob\\DueReminderJob"]):"MISSING";
$newJob=tableExists($db,$jobs)?scalar($db,"SELECT COUNT(*) FROM `".$jobs."` WHERE class = ?",["OCA\\HcStickyNotes\\BackgroundJob\\DueReminderJob"]):"MISSING";
echo str_pad("legacy due job",36)." : ".$oldJob."\n";
echo str_pad("new due job",36)." : ".$newJob."\n";
foreach ([["notes","stickynotes_notes","hc_stickynotes_notes"],["categories","stickynotes_categories","hc_stickynotes_categories"],["shares","stickynotes_shares","hc_stickynotes_shares"],["category_shares","stickynotes_category_shares","hc_stickynotes_category_shares"]] as [$label,$old,$new]) {
    echo str_pad("missing ids[$label]",36)." : ".missingIds($db,$prefix.$old,$prefix.$new)."\n";
}
'
