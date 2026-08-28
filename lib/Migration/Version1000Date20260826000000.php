<?php

declare(strict_types=1);

namespace OCA\StickyNotes\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20260826000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();
        if (!$schema->hasTable('stickynotes_notes')) {
            $table = $schema->createTable('stickynotes_notes');
            $table->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $table->addColumn('owner_uid', 'string', ['notnull' => true, 'length' => 64]);
            $table->addColumn('title', 'string', ['notnull' => true, 'length' => 255, 'default' => '']);
            $table->addColumn('content', 'text', ['notnull' => true, 'default' => '']);
            $table->addColumn('color', 'string', ['notnull' => true, 'length' => 20, 'default' => '#4f86f7']);
            $table->addColumn('category_id', 'bigint', ['notnull' => false, 'unsigned' => true]);
            $table->addColumn('type', 'string', ['notnull' => true, 'length' => 20, 'default' => 'note']);
            $table->addColumn('priority', 'string', ['notnull' => true, 'length' => 20, 'default' => 'normal']);
            $table->addColumn('assigned_uid', 'string', ['notnull' => false, 'length' => 64]);
            $table->addColumn('due_at', 'bigint', ['notnull' => false]);
            $table->addColumn('completed_at', 'bigint', ['notnull' => false]);
            $table->addColumn('created_at', 'bigint', ['notnull' => true]);
            $table->addColumn('updated_at', 'bigint', ['notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'stickynotes_owner_idx');
            $table->addIndex(['assigned_uid'], 'stickynotes_assigned_idx');
            $table->addIndex(['category_id'], 'stickynotes_category_idx');
        }
        if (!$schema->hasTable('stickynotes_shares')) {
            $table = $schema->createTable('stickynotes_shares');
            $table->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $table->addColumn('note_id', 'bigint', ['notnull' => true, 'unsigned' => true]);
            $table->addColumn('share_type', 'string', ['notnull' => true, 'length' => 16]);
            $table->addColumn('share_with', 'string', ['notnull' => true, 'length' => 64]);
            $table->addColumn('permission', 'string', ['notnull' => true, 'length' => 16, 'default' => 'view']);
            $table->addColumn('created_at', 'bigint', ['notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['note_id'], 'stickynotes_share_note_idx');
            $table->addIndex(['share_type', 'share_with'], 'stickynotes_share_target_idx');
        }

        if (!$schema->hasTable('stickynotes_categories')) {
            $table = $schema->createTable('stickynotes_categories');
            $table->addColumn('id','bigint',['autoincrement'=>true,'notnull'=>true,'unsigned'=>true]);
            $table->addColumn('owner_uid','string',['notnull'=>false,'length'=>64]);
            $table->addColumn('name','string',['notnull'=>true,'length'=>100]);
            $table->addColumn('color','string',['notnull'=>true,'length'=>20,'default'=>'#4f86f7']);
            $table->addColumn('icon','string',['notnull'=>true,'length'=>32,'default'=>'']);
            $table->addColumn('is_system','boolean',['notnull'=>true,'default'=>false]);
            $table->addColumn('created_at','bigint',['notnull'=>true]);
            $table->addColumn('updated_at','bigint',['notnull'=>true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'],'stickynotes_cat_owner_idx');
        }
        if (!$schema->hasTable('stickynotes_category_shares')) {
            $table = $schema->createTable('stickynotes_category_shares');
            $table->addColumn('id','bigint',['autoincrement'=>true,'notnull'=>true,'unsigned'=>true]);
            $table->addColumn('category_id','bigint',['notnull'=>true,'unsigned'=>true]);
            $table->addColumn('share_type','string',['notnull'=>true,'length'=>16]);
            $table->addColumn('share_with','string',['notnull'=>true,'length'=>64]);
            $table->addColumn('created_at','bigint',['notnull'=>true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['category_id'],'stickynotes_catshare_cat_idx');
        }
        return $schema;
    }
}
