<?php
class Migration_20161205104437_RenameClicksToEvents extends Migration 
{
    const DESCRIPTION_RU = 'Переименование таблицы clicks в events';

    const DESCRIPTION_EN = 'Rename table clicks to events';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}clicks TO {$prefix}events";
        self::execute($sql);
        $sql = "ALTER TABLE {$prefix}events 
                ADD COLUMN event_type VARCHAR(255) NOT NULL DEFAULT 'click',
                ADD INDEX (event_type)";
        self::execute($sql);    
    }
}