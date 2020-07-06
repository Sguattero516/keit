<?php


class Migration_28 extends Migration {

    const DESCRIPTION_RU = 'Добавление permissions в таблицу users';

    const DESCRIPTION_EN = 'Add permissions to table users';

    public static function up()
    {
        self::execute('ALTER TABLE `' . self::getDb()->getPrefix() .  'users` ADD `permissions` VARCHAR( 250 ) NULL DEFAULT NULL ');
    }
}