<?php
class Migration_20161112142644_RenameTimestampsInNetworks extends Migration 
{
    const DESCRIPTION_RU = 'Переименование колонок (create_datetime, updated_datetime) в landings';

    const DESCRIPTION_EN = 'Rename (create_datetime, updated_datetime) in landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings 
            CHANGE COLUMN `create_datetime` `created_at` DATETIME NOT NULL,
            CHANGE COLUMN `update_datetime` `updated_at` DATETIME NOT NULL
            ";
        self::silentExecute($sql);
    }
}