<?php
class Migration_20160918130517_CreateClicks extends Migration
{
    const DESCRIPTION_RU = 'Создание таблицы clicks';

    const DESCRIPTION_EN = 'Create table clicks';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'clicks` (
          `click_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `sub_id` varchar(255) NOT NULL,
          `ts_id` int(10) unsigned DEFAULT NULL,
          `landing_id` int(10) unsigned DEFAULT NULL,
          `offer_id` int(10) unsigned DEFAULT NULL,
          `datetime` datetime NOT NULL,
          `campaign_id` int(10) unsigned NOT NULL,
          `stream_id` int(10) unsigned NOT NULL,
          `is_unique_stream` tinyint(1) NOT NULL,
          `is_unique_campaign` tinyint(1) NOT NULL,
          `is_bot` tinyint(1) NOT NULL,
          `is_using_proxy` tinyint(1) NOT NULL,
          `is_empty_referrer` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
          `ip_id` int(10) unsigned NOT NULL,
          `country_id` int(10) unsigned NOT NULL,
          `region_id` int(10) unsigned NOT NULL,
          `city_id` int(10) unsigned NOT NULL,
          `user_agent_id` int(10) unsigned NOT NULL,
          `language` varchar(2) DEFAULT NULL,
          `os_id` int(10) unsigned NOT NULL,
          `os_version_id` int(10) unsigned NOT NULL,
          `connection_type` varchar(20) DEFAULT NULL,
          `browser_id` int(10) unsigned NOT NULL,
          `browser_version_id` int(10) unsigned NOT NULL,
          `device_type` varchar(40) DEFAULT NULL,
          `device_model_id` int(10) unsigned NOT NULL,
          `isp_id` int(10) unsigned NOT NULL,
          `operator_id` int(10) unsigned DEFAULT NULL,
          `source_id` int(10) unsigned NOT NULL,
          `referrer_id` int(10) NOT NULL,
          `se_id` int(10) unsigned NOT NULL,
          `keyword_id` int(10) unsigned DEFAULT NULL,
          `screen_id` int(10) unsigned DEFAULT NULL,
          `sub_id_1_id` int(10) unsigned DEFAULT NULL,
          `sub_id_2_id` int(10) unsigned DEFAULT NULL,
          `sub_id_3_id` int(10) unsigned DEFAULT NULL,
          `sub_id_4_id` int(10) unsigned DEFAULT NULL,
          `sub_id_5_id` int(10) unsigned DEFAULT NULL,
          `sub_id_6_id` int(10) unsigned DEFAULT NULL,
          `sub_id_7_id` int(10) unsigned DEFAULT NULL,
          `sub_id_8_id` int(10) unsigned DEFAULT NULL,
          `sub_id_9_id` int(10) unsigned DEFAULT NULL,
          `sub_id_10_id` int(10) unsigned DEFAULT NULL,
          `extra_param_1` varchar(255) DEFAULT NULL,
          `extra_param_2` varchar(255) DEFAULT NULL,
          `extra_param_3` varchar(255) DEFAULT NULL,
          `extra_param_4` varchar(255) DEFAULT NULL,
          `extra_param_5` varchar(255) DEFAULT NULL,
          `extra_param_6` varchar(255) DEFAULT NULL,
          `extra_param_7` varchar(255) DEFAULT NULL,
          `extra_param_8` varchar(255) DEFAULT NULL,
          `extra_param_9` varchar(255) DEFAULT NULL,
          `extra_param_10` varchar(255) DEFAULT NULL,
          `revenue` decimal(13,4) NOT NULL DEFAULT \'0.0000\',
          `cost` decimal(13,4) NOT NULL DEFAULT \'0.0000\',
          `is_lead` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
          `is_sale` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
          `is_rejected` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
          `rebills` int(10) unsigned NOT NULL DEFAULT \'0\',
          `destination` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`click_id`),
          UNIQUE KEY `sub_id` (`sub_id`),
          KEY `visited_at_campaign_id_steam_id` (`datetime`,`campaign_id`,`stream_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

        self::execute($sql);
    }
}