<?php


class Migration_22 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки scan_page в triggers';

    const DESCRIPTION_EN = 'Add field scan_page to triggers';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'triggers ADD `scan_page` TINYINT( 1 ) NOT NULL DEFAULT \'0\'';
        self::execute($sql);
    }
}