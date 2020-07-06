<?php
class Migration_87 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы groups';

    const DESCRIPTION_EN = 'Create table groups';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "CREATE TABLE `{$prefix}groups` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `name` VARCHAR( 100 ) NOT NULL ,
            `position` INT UNSIGNED NOT NULL ,
            INDEX ( `position` )
            ) ENGINE = InnoDB;
        ";
        self::execute($sql);
    }
}