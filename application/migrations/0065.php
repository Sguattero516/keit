<?php
class Migration_65 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы index_campaigns и копирование данных';

    const DESCRIPTION_EN = 'Create table index_campaigns and copy data';

    public static function up()
    {
        $table = self::getDb()->getPrefix() . 'index_campaigns';
        $oldTable = self::getDb()->getPrefix() . 'archive_visits';

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table. ' (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` int(10) unsigned NOT NULL,
              `date` date NOT NULL,
              `count` int(10) unsigned DEFAULT 0,
              `unique` int(10) unsigned DEFAULT 0,
              `bots` int(10) unsigned DEFAULT 0,
              `empty_referrer` int(10) unsigned DEFAULT 0,
              `mobile` int(10) unsigned DEFAULT 0,
              `leads` int(10) unsigned DEFAULT 0,
              `sales` int(10) unsigned DEFAULT 0,
              `revenue` DECIMAL(13, 4) unsigned DEFAULT 0,
              `cost` DECIMAL(13, 4) unsigned DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `date_campaign_id` (`date`, `campaign_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
        self::execute($sql);

        $sql = 'INSERT INTO ' . $table. ' (campaign_id, date, `count`, `unique`, `bots`, `empty_referrer`, `mobile`)
            SELECT group_id, date, `raws`, `uniq`, `bots`, `empty_referer`, `mobile` FROM ' . $oldTable .'';
        self::execute($sql);
    }
}