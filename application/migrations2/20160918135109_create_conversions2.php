<?php
class Migration_20160918135109_CreateConversions2 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы conversion_2';

    const DESCRIPTION_EN = 'Create table conversions_2';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'conversions_2` (
          `conversion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `campaign_id` int(11) DEFAULT NULL,
          `stream_id` int(10) unsigned DEFAULT NULL,
          `ts_id` int(10) unsigned DEFAULT NULL,
          `landing_id` int(10) unsigned DEFAULT NULL,
          `offer_id` int(10) unsigned DEFAULT NULL,
          `sub_id` varchar(255) NOT NULL,
          `tid` varchar(100) DEFAULT NULL,
          `click_datetime` datetime NOT NULL,
          `postback_datetime` datetime NOT NULL,
          `status` varchar(100) DEFAULT NULL,
          `previous_status` varchar(100) DEFAULT NULL,
          `original_status` varchar(100) DEFAULT NULL,
          `ip_id` int(10) unsigned DEFAULT NULL,
          `country_id` int(10) unsigned DEFAULT NULL,
          `region_id` int(10) unsigned DEFAULT NULL,
          `city_id` int(10) unsigned DEFAULT NULL,
          `user_agent_id` int(10) unsigned DEFAULT NULL,
          `language` varchar(2) DEFAULT NULL,
          `os_id` int(10) unsigned DEFAULT NULL,
          `os_version_id` int(10) unsigned DEFAULT NULL,
          `connection_type` varchar(20) DEFAULT NULL,
          `browser_id` int(10) unsigned DEFAULT NULL,
          `browser_version_id` int(10) unsigned DEFAULT NULL,
          `device_type` varchar(40) DEFAULT NULL,
          `device_model_id` int(10) unsigned DEFAULT NULL,
          `isp_id` int(10) unsigned DEFAULT NULL,
          `operator_id` int(10) unsigned DEFAULT NULL,
          `source_id` int(10) unsigned DEFAULT NULL,
          `referrer_id` int(10) unsigned DEFAULT NULL,
          `se_id` int(10) unsigned DEFAULT NULL,
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
          `revenue` decimal(10,4) DEFAULT \'0.0000\',
          `cost` decimal(10,4) DEFAULT \'0.0000\',
          `params` text,
          `is_processed` int(1) unsigned NOT NULL DEFAULT \'0\',
          PRIMARY KEY (`conversion_id`),
          UNIQUE KEY `sub_id_tid` (`sub_id`,`tid`),
          KEY `campaign_id` (`campaign_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

        self::execute($sql);
    }
}