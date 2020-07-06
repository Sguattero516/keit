<?php


class Migration_38 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки sales в archive_sources и archive_streams';

    const DESCRIPTION_EN = 'Add field sales to archive_sources and archive_streams';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'archive_streams ADD `sales` INTEGER UNSIGNED NOT NULL DEFAULT \'0\'';
        self::silentExecute($sql);

        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'archive_sources ADD `sales` INTEGER UNSIGNED NOT NULL DEFAULT \'0\'';
        self::silentExecute($sql);

    }
}