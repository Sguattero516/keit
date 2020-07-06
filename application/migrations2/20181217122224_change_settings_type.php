<?php
class Migration_20181217122224_ChangeSettingsType extends Migration 
{
    const DESCRIPTION_RU = 'Расширение settings.value';

    const DESCRIPTION_EN = 'Extend settings.value';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}settings CHANGE COLUMN `value` `value` TEXT DEFAULT NULL";
        self::execute($sql);
    }
}