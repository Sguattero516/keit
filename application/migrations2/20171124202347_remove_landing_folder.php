<?php
class Migration_20171124202347_RemoveLandingFolder extends Migration 
{
    const DESCRIPTION_RU = 'Удаление folder в landings';

    const DESCRIPTION_EN = 'Delete folder from landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings DROP COLUMN folder";
        self::silentExecute($sql);
    }
}