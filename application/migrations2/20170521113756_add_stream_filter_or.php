<?php
class Migration_20170521113756_AddStreamFilterOr extends Migration 
{
    const DESCRIPTION_RU = 'Добавление filter_or к streams';

    const DESCRIPTION_EN = 'Add filter_or to streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}streams 
                ADD filter_or INT(1) UNSIGNED NULL DEFAULT '0'";
        self::execute($sql);
    }
}