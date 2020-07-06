<?php
class Migration_20161012092340_CreateLanding extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы landing';

    const DESCRIPTION_EN = 'Create table landing*';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'landings` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `url` text NOT NULL,
          `group_id` bigint(20) unsigned,
          `offer_count`  int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}