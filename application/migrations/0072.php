<?php
class Migration_72 extends Migration {

    const DESCRIPTION_RU = 'Добавление updated_at в streams и campaigns';

    const DESCRIPTION_EN = 'Add updated_at to streams and campaigns';

    public static function up()
    {
        self::silentExecute('ALTER TABLE `' .  self::getDb()->getPrefix() . 'streams` ADD `updated_at` DATETIME NULL DEFAULT NULL ');
        self::silentExecute('ALTER TABLE `' .  self::getDb()->getPrefix() . 'campaigns` ADD `updated_at` DATETIME NULL DEFAULT NULL ');
    }
}