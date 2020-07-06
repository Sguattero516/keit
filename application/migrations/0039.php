<?php


class Migration_39 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы stream_filters';

    const DESCRIPTION_EN = 'Create table stream_filters';

    public static function up()
    {
        $sql = 'CREATE TABLE `' . self::getDb()->getPrefix() . 'stream_filters` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `stream_id` INT UNSIGNED NOT NULL ,
            `name` VARCHAR( 100 ) NOT NULL ,
            `mode` enum(\'accept\',\'reject\') NOT NULL,
            `payload` TEXT NOT NULL ,
            INDEX ( `stream_id` )
            ) ENGINE = InnoDB;';
        self::execute($sql);
    }
}