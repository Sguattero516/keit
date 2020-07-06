<?php


class Migration_2 extends Migration {

    const DESCRIPTION_RU = 'Добавление параметра progressive_filters_enabled';

    const DESCRIPTION_EN = 'Add parameter progressive_filters_enabled';

    public static function up()
    {
        $sql = "INSERT IGNORE INTO " . self::getDb()->getPrefix() . "settings (`key`, `value`) VALUES ('progressive_filters_enabled', '1')";
        self::getDb()->execute($sql);
    }
}
