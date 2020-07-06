<?php


class Migration_21 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки advertising_mode в stream_groups';

    const DESCRIPTION_EN = 'Add field advertising_mode to stream_groups';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'stream_groups ADD `advertising_mode` INT(1) UNSIGNED NOT NULL DEFAULT \'0\'';
        self::execute($sql);
    }
}