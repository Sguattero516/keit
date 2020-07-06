<?php
class Migration_20171222190026_DisableExtraParam extends Migration 
{
    const DESCRIPTION_RU = 'Добавление настройки "Показывать extra_param"';

    const DESCRIPTION_EN = 'Add setting "Show extra_param';

    public static function up()
    {
        $sql = "INSERT IGNORE INTO " . self::getDb()->getPrefix() . "settings (`key`, `value`) VALUES ('show_extra_param', '1')";
        self::getDb()->execute($sql);
    }
}