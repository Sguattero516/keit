<?php
class Migration_20161031233854_CreateAffiliateNetwork extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы affiliate_networks';

    const DESCRIPTION_EN = 'Create table affiliate_networks';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'affiliate_networks` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `postback_url` text  NOT NULL,
          `state` varchar(255),
          `template_name` varchar(255),
          `create_datetime` DATETIME NOT NULL,
          `update_datetime` DATETIME NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}