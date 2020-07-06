<?php
class Migration_20160918142331_CreateDailyEntries extends Migration 
{

    const DESCRIPTION_RU = 'Создание таблицы daily_entries';

    const DESCRIPTION_EN = 'Create table daily_entries';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'daily_entries` (
          `entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `date` date NOT NULL,
          `campaign_id` int(10) unsigned NOT NULL,
          `field_name` varchar(100) NOT NULL,
          `field_value` int(10) unsigned NOT NULL,
          `clicks` int(10) unsigned NOT NULL DEFAULT \'0\',
          `unique_clicks` int(10) unsigned NOT NULL DEFAULT \'0\',
          `bots` int(10) unsigned NOT NULL DEFAULT \'0\',
          `mobiles` int(10) unsigned NOT NULL DEFAULT \'0\',
          `empty_referrers` int(10) unsigned NOT NULL DEFAULT \'0\',
          `proxies` int(10) unsigned NOT NULL DEFAULT \'0\',
          `conversions` int(10) unsigned NOT NULL DEFAULT \'0\',
          `leads` int(10) unsigned NOT NULL DEFAULT \'0\',
          `sales` int(10) unsigned NOT NULL DEFAULT \'0\',
          `rejected` int(10) unsigned NOT NULL DEFAULT \'0\',
          `rebills` int(10) unsigned NOT NULL DEFAULT \'0\',
          `revenue` decimal(13,4) unsigned NOT NULL DEFAULT \'0.0000\',
          `cost` decimal(13,4) unsigned NOT NULL DEFAULT \'0.0000\',
          PRIMARY KEY (`entry_id`),
          UNIQUE KEY `date_campaign_name_value` (`date`,`campaign_id`,`field_name`,`field_value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}