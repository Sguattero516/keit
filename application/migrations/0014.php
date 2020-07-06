<?php


class Migration_14 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонок region_filter и region_filter_value';

    const DESCRIPTION_EN = 'Add columns region_filter and region_filter_value';

    public static function up()
    {
        $sql = "ALTER TABLE `". self::getDb()->getPrefix() . "streams` ADD `regions_filter` ENUM( '', 'allow', 'block' ) NOT NULL ,
            ADD `regions_filter_value` TEXT NULL";
        self::silentExecute($sql);
    }
}