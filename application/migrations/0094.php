<?php
class Migration_94 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы queue';

    const DESCRIPTION_EN = 'Create table queue';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}queue (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `data` BLOB NOT NULL,
            `datetime` DATETIME DEFAULT NULL,
            `status` VARCHAR( 255 ) NOT NULL,
            `error_message` VARCHAR( 255 ) DEFAULT NULL ,
            INDEX ( `status` )
            ) ENGINE = INNODB;";

        self::execute($sql);
    }
}