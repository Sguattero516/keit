<?php
class Migration_20161108142454_CreateStreamLandingAssociations extends Migration 
{
    const DESCRIPTION_RU = 'Создание stream_landing_associations';

    const DESCRIPTION_EN = 'Create table stream_landing_associations';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}stream_landing_associations (
          `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
          `stream_id` int(20) unsigned NOT NULL,
          `landing_id` int(20) unsigned NOT NULL,
          `state` varchar(10) NOT NULL,
          `share` int(10) NOT NULL,
          `created_at` DATETIME NOT NULL,
          `updated_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `stream_landing` (`stream_id`,`landing_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        self::execute($sql);
    }
}