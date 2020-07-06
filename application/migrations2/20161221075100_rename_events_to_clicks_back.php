<?php
class Migration_20161221075100_RenameEventsToClicksBack extends Migration 
{
    const DESCRIPTION_RU = 'Переименование events в clicks';

    const DESCRIPTION_EN = 'Rename events в clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}events TO {$prefix}clicks";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}clicks DROP COLUMN event_type";
        self::execute($sql);
    }
}