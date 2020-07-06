<?php
class Migration_20161107152857_CreateOffersTable extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы offers';

    const DESCRIPTION_EN = 'Create table offers';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'offers` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `group_id` bigint(20) unsigned,
          `url` text NOT NULL,
          `affiliate_network_id` bigint(20) unsigned,
          `payout_value` decimal(10,4) DEFAULT "0.0000",
          `payout_currency` varchar(3),
          `payout_type` varchar(10),
          `state` varchar(255),
          `create_datetime` DATETIME NOT NULL,
          `update_datetime` DATETIME NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}