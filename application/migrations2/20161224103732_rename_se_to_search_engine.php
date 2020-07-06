<?php
class Migration_20161224103732_RenameSeToSearchEngine extends Migration 
{
    const DESCRIPTION_RU = 'Переименовать clicks.se_id в clicks.search_engine_id';

    const DESCRIPTION_EN = 'Rename clicks.se_id to clicks.search_engine_id';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks CHANGE COLUMN se_id search_engine_id int(10) DEFAULT NULL";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}conversions_2 CHANGE COLUMN se_id search_engine_id int(10) DEFAULT NULL";
        self::execute($sql);
    }
}