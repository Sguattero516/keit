<?php
class Migration_20161108190417_RenameTimestampsInLandings extends Migration 
{
    const DESCRIPTION_RU = 'Переименование колонок (create_datetime, updated_datetime) в affiliate_networks';

    const DESCRIPTION_EN = 'Rename (create_datetime, updated_datetime) in affiliate_networks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}affiliate_networks 
            CHANGE COLUMN `create_datetime` `created_at` DATETIME NOT NULL,
            CHANGE COLUMN `update_datetime` `updated_at` DATETIME NOT NULL
            ";
        self::execute($sql);
    }
}