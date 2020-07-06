<?php


class Migration_15 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки conversion_tracking_enabled';

    const DESCRIPTION_EN = 'Add field conversion_tracking_enabled';

    public static function up()
    {
        $sql = "ALTER TABLE `". self::getDb()->getPrefix() . "stream_groups` ADD `conversion_tracking_enabled` TINYINT( 1 ) NULL DEFAULT NULL";
        self::execute($sql);
    }
}