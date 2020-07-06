<?php


class Migration_19 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки disable_tracking_exit';

    const DESCRIPTION_EN = 'Add field disable_tracking_exit';

    public static function up()
    {
        $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'stream_groups` ADD `disable_tracking_exit` TINYINT( 1 )  UNSIGNED NOT NULL DEFAULT \'0\'';
        self::silentExecute($sql);
    }
}