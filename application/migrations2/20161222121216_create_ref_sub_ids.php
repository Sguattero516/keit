<?php
class Migration_20161222121216_CreateRefSubIds extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы ref_sub_ids';

    const DESCRIPTION_EN = 'Create tables ref_sub_ids';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}ref_sub_ids (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}