<?php
class Migration_20161108142459_CreateStreamOfferAssociations extends Migration 
{
    const DESCRIPTION_RU = 'Создание stream_offer_associations';

    const DESCRIPTION_EN = 'Create table stream_offer_associations';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}stream_offer_associations (
          `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
          `stream_id` int(20) unsigned NOT NULL,
          `offer_id` int(20) unsigned NOT NULL,
          `state` varchar(10) NOT NULL,
          `share` int(10) NOT NULL,
          `created_at` DATETIME NOT NULL,
          `updated_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `stream_offer` (`stream_id`,`offer_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        self::execute($sql);
    }
}