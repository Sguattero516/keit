<?php
class Migration_73 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы requests';

    const DESCRIPTION_EN = 'Create table requests';

    public static function up()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . self::getDb()->getPrefix() . 'requests (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `stream_id` INT UNSIGNED NOT NULL ,
            `datetime` DATETIME NOT NULL
            ) ENGINE = InnoDB;';
        self::execute($sql);
    }
}