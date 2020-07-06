<?php


class Migration_20 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки conversion в archive_streams';

    const DESCRIPTION_EN = 'Add field conversion to archive_streams';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'archive_streams ADD `conversion` DECIMAL( 5, 2 ) UNSIGNED NOT NULL DEFAULT \'0\'';
        self::execute($sql);
    }
}