<?php
class Migration_20170120210133_AddSettingStatsTtl extends Migration 
{
    const DESCRIPTION_RU = 'Добавление stats_ttl в settings';

    const DESCRIPTION_EN = 'Add stats_ttl to settings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "INSERT INTO {$prefix}settings (`key`, `value`) values ('stats_ttl', 0)";
        self::execute($sql);
    }
}