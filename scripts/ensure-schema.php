<?php
declare(strict_types=1);

// Re-run on EVERY install, including an interrupted install at the same version.
// Only append absent application tables, columns and indexes. Never drop data.
require '/var/www/html/lib/base.php';
$db = \OC::$server->get(\OCP\IDBConnection::class);
$prefix = (string)\OC::$server->get(\OCP\IConfig::class)->getSystemValue('dbtableprefix', 'oc_');
if (!preg_match('/^[A-Za-z0-9_]+$/D', $prefix)) {
    throw new \RuntimeException('Invalid Nextcloud table prefix');
}
$schema = $db->createSchema();
$changed = false;
$addColumn = static function ($table, string $name, string $type, array $options = []) use (&$changed): void {
    if (!$table->hasColumn($name)) {
        $table->addColumn($name, $type, $options);
        $changed = true;
    }
};
$addIndex = static function ($table, array $columns, string $name, bool $unique = false) use (&$changed): void {
    if (!$table->hasIndex($name)) {
        if ($unique) $table->addUniqueIndex($columns, $name);
        else $table->addIndex($columns, $name);
        $changed = true;
    }
};
$setPrimaryKey = static function ($table, array $columns) use (&$changed): void {
    if (!$table->hasPrimaryKey()) {
        $table->setPrimaryKey($columns);
        $changed = true;
    }
};

// hc_stickynotes_notes
if (!$schema->hasTable($prefix . 'hc_stickynotes_notes')) $changed = true;
{
$table = $schema->hasTable($prefix . 'hc_stickynotes_notes') ? $schema->getTable($prefix . 'hc_stickynotes_notes') : $schema->createTable($prefix . 'hc_stickynotes_notes');
            $addColumn($table, 'id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $addColumn($table, 'owner_uid', 'string', ['notnull' => true, 'length' => 64]);
            $addColumn($table, 'title', 'string', ['notnull' => true, 'length' => 255, 'default' => '']);
            $addColumn($table, 'content', 'text', ['notnull' => true, 'default' => '']);
            $addColumn($table, 'color', 'string', ['notnull' => true, 'length' => 20, 'default' => '#4f86f7']);
            $addColumn($table, 'category_id', 'bigint', ['notnull' => false, 'unsigned' => true]);
            $addColumn($table, 'type', 'string', ['notnull' => true, 'length' => 20, 'default' => 'note']);
            $addColumn($table, 'priority', 'string', ['notnull' => true, 'length' => 20, 'default' => 'normal']);
            $addColumn($table, 'assigned_uid', 'string', ['notnull' => false, 'length' => 64]);
            $addColumn($table, 'due_at', 'bigint', ['notnull' => false]);
            $addColumn($table, 'completed_at', 'bigint', ['notnull' => false]);
            $addColumn($table, 'created_at', 'bigint', ['notnull' => true]);
            $addColumn($table, 'updated_at', 'bigint', ['notnull' => true]);
            $setPrimaryKey($table, ['id']);
            $addIndex($table, ['owner_uid'], 'hc_stickynotes_owner_idx');
            $addIndex($table, ['assigned_uid'], 'hc_stickynotes_assigned_idx');
            $addIndex($table, ['category_id'], 'hc_stickynotes_category_idx');
}

// hc_stickynotes_shares
if (!$schema->hasTable($prefix . 'hc_stickynotes_shares')) $changed = true;
{
$table = $schema->hasTable($prefix . 'hc_stickynotes_shares') ? $schema->getTable($prefix . 'hc_stickynotes_shares') : $schema->createTable($prefix . 'hc_stickynotes_shares');
            $addColumn($table, 'id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $addColumn($table, 'note_id', 'bigint', ['notnull' => true, 'unsigned' => true]);
            $addColumn($table, 'share_type', 'string', ['notnull' => true, 'length' => 16]);
            $addColumn($table, 'share_with', 'string', ['notnull' => true, 'length' => 64]);
            $addColumn($table, 'permission', 'string', ['notnull' => true, 'length' => 16, 'default' => 'view']);
            $addColumn($table, 'created_at', 'bigint', ['notnull' => true]);
            $setPrimaryKey($table, ['id']);
            $addIndex($table, ['note_id'], 'hc_stickynotes_share_note_idx');
            $addIndex($table, ['share_type', 'share_with'], 'hc_stickynotes_share_target_idx');
}

// hc_stickynotes_categories
if (!$schema->hasTable($prefix . 'hc_stickynotes_categories')) $changed = true;
{
$table = $schema->hasTable($prefix . 'hc_stickynotes_categories') ? $schema->getTable($prefix . 'hc_stickynotes_categories') : $schema->createTable($prefix . 'hc_stickynotes_categories');
            $addColumn($table, 'id','bigint',['autoincrement'=>true,'notnull'=>true,'unsigned'=>true]);
            $addColumn($table, 'owner_uid','string',['notnull'=>false,'length'=>64]);
            $addColumn($table, 'name','string',['notnull'=>true,'length'=>100]);
            $addColumn($table, 'color','string',['notnull'=>true,'length'=>20,'default'=>'#4f86f7']);
            $addColumn($table, 'icon','string',['notnull'=>true,'length'=>32,'default'=>'']);
            $addColumn($table, 'is_system','boolean',['notnull'=>true,'default'=>false]);
            $addColumn($table, 'created_at','bigint',['notnull'=>true]);
            $addColumn($table, 'updated_at','bigint',['notnull'=>true]);
            $setPrimaryKey($table, ['id']);
            $addIndex($table, ['owner_uid'],'hc_stickynotes_cat_owner_idx');
}

// hc_stickynotes_category_shares
if (!$schema->hasTable($prefix . 'hc_stickynotes_category_shares')) $changed = true;
{
$table = $schema->hasTable($prefix . 'hc_stickynotes_category_shares') ? $schema->getTable($prefix . 'hc_stickynotes_category_shares') : $schema->createTable($prefix . 'hc_stickynotes_category_shares');
            $addColumn($table, 'id','bigint',['autoincrement'=>true,'notnull'=>true,'unsigned'=>true]);
            $addColumn($table, 'category_id','bigint',['notnull'=>true,'unsigned'=>true]);
            $addColumn($table, 'share_type','string',['notnull'=>true,'length'=>16]);
            $addColumn($table, 'share_with','string',['notnull'=>true,'length'=>64]);
            $addColumn($table, 'created_at','bigint',['notnull'=>true]);
            $setPrimaryKey($table, ['id']);
            $addIndex($table, ['category_id'],'hc_stickynotes_catshare_cat_idx');
}

$required = [];
foreach (['hc_stickynotes_notes', 'hc_stickynotes_shares', 'hc_stickynotes_categories', 'hc_stickynotes_category_shares'] as $logical) {
    $table = $schema->getTable($prefix . $logical);
    $required[$logical] = ['columns' => array_keys($table->getColumns()), 'indexes' => array_diff(array_keys($table->getIndexes()), ['primary'])];
}

if ($changed) {
    $db->migrateToSchema($schema);
}
// Verify from a fresh snapshot; metadata in $schema alone is insufficient.
$verified = $db->createSchema();
foreach ($required as $logical => $parts) {
    $physical = $prefix . $logical;
    if (!$verified->hasTable($physical)) throw new \RuntimeException('Missing table: ' . $physical);
    $table = $verified->getTable($physical);
    foreach ($parts['columns'] as $column) {
        if (!$table->hasColumn($column)) throw new \RuntimeException('Missing column: ' . $physical . '.' . $column);
    }
    foreach ($parts['indexes'] as $index) {
        if (!$table->hasIndex($index)) throw new \RuntimeException('Missing index: ' . $physical . '.' . $index);
    }
}
echo "Database schema: OK (" . count($required) . " tables)\n";
