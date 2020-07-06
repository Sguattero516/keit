<?php
class Migration_20180118121205_AddIsSidebarEnabled extends Migration 
{
    const DESCRIPTION_RU = 'Добавление настройки is_sidebar_enabled';

    const DESCRIPTION_EN = 'Add setting is_sidebar_enabled';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "INSERT IGNORE INTO {$prefix}settings (`key`, `value`) values ('is_sidebar_enabled', '1')";
        self::execute($sql);
    }
}