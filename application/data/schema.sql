CREATE TABLE IF NOT EXISTS `keitaro_acl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `access_type` varchar(255) NOT NULL,
  `groups` text DEFAULT NULL,
  `entities` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `entity_type` (`entity_type`),
  KEY `access_type` (`access_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_acl_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `columns` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_acl_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `resources` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_affiliate_networks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `postback_url` text DEFAULT NULL,
  `offer_param` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_branding` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `logo` longblob DEFAULT NULL,
  `favicon` longblob DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'position',
  `uniqueness_method` varchar(20) NOT NULL DEFAULT 'ip_ua',
  `cookies_ttl` int(11) NOT NULL DEFAULT 24,
  `action_type` varchar(20) DEFAULT NULL,
  `action_payload` text DEFAULT NULL,
  `action_for_bots` varchar(50) NOT NULL DEFAULT '404',
  `bot_redirect_url` text DEFAULT NULL,
  `bot_text` text DEFAULT NULL,
  `action_tracking_disabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `position` int(10) unsigned DEFAULT NULL,
  `state` varchar(50) NOT NULL DEFAULT 'active',
  `updated_at` datetime DEFAULT NULL,
  `mode` varchar(50) DEFAULT 'general',
  `cost_type` varchar(10) DEFAULT 'CPV',
  `cost_value` decimal(10,4) DEFAULT 0.0000,
  `cost_currency` varchar(3) DEFAULT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `bind_visitors` varchar(255) DEFAULT NULL,
  `traffic_source_id` int(10) unsigned DEFAULT NULL,
  `token` varchar(50) DEFAULT NULL,
  `cost_auto` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `domain_id` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `uniqueness_use_cookies` int(1) unsigned DEFAULT 1,
  `traffic_loss` decimal(4,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `alias` (`alias`),
  KEY `state` (`state`),
  KEY `group_id` (`group_id`),
  KEY `traffic_source_id` (`traffic_source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_campaign_postbacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `method` varchar(10) NOT NULL,
  `url` text NOT NULL,
  `statuses` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_clicks` (
  `click_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` bigint(20) unsigned NOT NULL,
  `sub_id` varchar(255) NOT NULL,
  `ts_id` int(10) unsigned DEFAULT NULL,
  `landing_id` int(10) unsigned DEFAULT NULL,
  `landing_clicked` tinyint(1) unsigned DEFAULT 0,
  `landing_clicked_datetime` datetime DEFAULT NULL,
  `offer_id` int(10) unsigned DEFAULT NULL,
  `affiliate_network_id` int(10) unsigned DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `parent_campaign_id` int(10) unsigned DEFAULT NULL,
  `stream_id` int(10) unsigned DEFAULT NULL,
  `is_unique_stream` tinyint(1) unsigned DEFAULT 0,
  `is_unique_campaign` tinyint(1) unsigned DEFAULT 0,
  `is_unique_global` int(1) unsigned DEFAULT 0,
  `is_bot` tinyint(1) unsigned DEFAULT 0,
  `is_using_proxy` tinyint(1) unsigned DEFAULT 0,
  `is_empty_referrer` tinyint(1) unsigned DEFAULT 0,
  `source_id` int(10) unsigned NOT NULL,
  `referrer_id` int(10) NOT NULL,
  `search_engine_id` int(10) DEFAULT NULL,
  `keyword_id` int(10) unsigned DEFAULT NULL,
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
  `sub_id_11_id` int(10) unsigned DEFAULT NULL,
  `sub_id_12_id` int(10) unsigned DEFAULT NULL,
  `sub_id_13_id` int(10) unsigned DEFAULT NULL,
  `sub_id_14_id` int(10) unsigned DEFAULT NULL,
  `sub_id_15_id` int(10) unsigned DEFAULT NULL,
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
  `lead_revenue` decimal(13,4) NOT NULL DEFAULT 0.0000,
  `rejected_revenue` decimal(13,4) NOT NULL DEFAULT 0.0000,
  `sale_revenue` decimal(13,4) NOT NULL DEFAULT 0.0000,
  `cost` decimal(13,6) NOT NULL DEFAULT 0.000000,
  `is_lead` tinyint(1) unsigned DEFAULT 0,
  `is_sale` tinyint(1) unsigned DEFAULT 0,
  `is_rejected` tinyint(1) unsigned DEFAULT 0,
  `rebills` int(10) unsigned NOT NULL DEFAULT 0,
  `destination_id` bigint(20) unsigned DEFAULT NULL,
  `creative_id_id` int(10) unsigned DEFAULT NULL,
  `external_id_id` int(10) unsigned DEFAULT NULL,
  `goal_1` tinyint(1) unsigned DEFAULT 0,
  `goal_1_datetime` datetime DEFAULT NULL,
  `goal_2` tinyint(1) unsigned DEFAULT 0,
  `goal_2_datetime` datetime DEFAULT NULL,
  `goal_3` tinyint(1) unsigned DEFAULT 0,
  `goal_3_datetime` datetime DEFAULT NULL,
  `goal_4` tinyint(1) unsigned DEFAULT 0,
  `goal_4_datetime` datetime DEFAULT NULL,
  `ad_campaign_id_id` int(10) unsigned DEFAULT NULL,
  `x_requested_with_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`click_id`),
  UNIQUE KEY `sub_id` (`sub_id`),
  KEY `datetime` (`datetime`),
  KEY `creative_id` (`creative_id_id`),
  KEY `external_id` (`external_id_id`),
  KEY `landing_id` (`landing_id`),
  KEY `offer_id` (`offer_id`),
  KEY `stream_id` (`stream_id`),
  KEY `keyword_id` (`keyword_id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `ts_id` (`ts_id`),
  KEY `sub_id_1_id` (`sub_id_1_id`),
  KEY `sub_id_2_id` (`sub_id_2_id`),
  KEY `sub_id_3_id` (`sub_id_3_id`),
  KEY `sub_id_4_id` (`sub_id_4_id`),
  KEY `sub_id_5_id` (`sub_id_5_id`),
  KEY `sub_id_6_id` (`sub_id_6_id`),
  KEY `sub_id_7_id` (`sub_id_7_id`),
  KEY `sub_id_8_id` (`sub_id_8_id`),
  KEY `sub_id_9_id` (`sub_id_9_id`),
  KEY `sub_id_10_id` (`sub_id_10_id`),
  KEY `destination_id` (`destination_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `source_id` (`source_id`),
  KEY `parent_campaign_id` (`parent_campaign_id`),
  KEY `search_engine_id` (`search_engine_id`),
  KEY `ad_campaign_id_id` (`ad_campaign_id_id`),
  KEY `campaign_id_datetime` (`campaign_id`,`datetime`),
  KEY `stream_id_datetime` (`stream_id`,`datetime`),
  KEY `ts_id_datetime` (`ts_id`,`datetime`),
  KEY `landing_id_datetime` (`landing_id`,`datetime`),
  KEY `offer_id_datetime` (`offer_id`,`datetime`),
  KEY `affiliate_network_id` (`affiliate_network_id`),
  KEY `sub_id_11_id` (`sub_id_11_id`),
  KEY `sub_id_12_id` (`sub_id_12_id`),
  KEY `sub_id_13_id` (`sub_id_13_id`),
  KEY `sub_id_14_id` (`sub_id_14_id`),
  KEY `sub_id_15_id` (`sub_id_15_id`),
  KEY `x_requested_with_id` (`x_requested_with_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_click_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sub_id` varchar(255) NOT NULL,
  `parent_sub_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_ids` (`sub_id`,`parent_sub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_command_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` date DEFAULT NULL,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_conversions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `stream_id` int(10) unsigned DEFAULT NULL,
  `sub_id` varchar(30) NOT NULL,
  `tid` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `previous_status` varchar(100) DEFAULT NULL,
  `original_status` varchar(100) DEFAULT NULL,
  `se` varchar(100) DEFAULT NULL,
  `sub_id_1` varchar(255) DEFAULT NULL,
  `sub_id_2` varchar(255) DEFAULT NULL,
  `sub_id_3` varchar(255) DEFAULT NULL,
  `sub_id_4` varchar(255) DEFAULT NULL,
  `extra_param_1` varchar(255) DEFAULT NULL,
  `extra_param_2` varchar(255) DEFAULT NULL,
  `extra_param_3` varchar(255) DEFAULT NULL,
  `extra_param_4` varchar(255) DEFAULT NULL,
  `extra_param_5` varchar(255) DEFAULT NULL,
  `extra_param_6` varchar(255) DEFAULT NULL,
  `extra_param_7` varchar(255) DEFAULT NULL,
  `revenue` decimal(10,4) DEFAULT 0.0000,
  `cost` decimal(10,4) DEFAULT 0.0000,
  `browser` varchar(150) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `region` varchar(6) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip` bigint(11) DEFAULT NULL,
  `keyword` varchar(250) DEFAULT NULL,
  `source` varchar(250) DEFAULT NULL,
  `isp` varchar(50) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `connection_type` varchar(10) DEFAULT NULL,
  `device_type` varchar(30) DEFAULT NULL,
  `device_model` varchar(200) DEFAULT NULL,
  `params` text DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `entry_date` date NOT NULL,
  `processed` int(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_id_tid` (`sub_id`,`tid`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_conversions_2` (
  `conversion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` bigint(20) unsigned DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `stream_id` int(10) unsigned DEFAULT NULL,
  `ts_id` int(10) unsigned DEFAULT NULL,
  `landing_id` int(10) unsigned DEFAULT NULL,
  `offer_id` int(10) unsigned DEFAULT NULL,
  `affiliate_network_id` int(10) unsigned DEFAULT NULL,
  `sub_id` varchar(255) NOT NULL,
  `click_id` int(10) unsigned DEFAULT NULL,
  `tid` varchar(100) DEFAULT NULL,
  `click_datetime` datetime NOT NULL,
  `postback_datetime` datetime NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `previous_status` varchar(100) DEFAULT NULL,
  `original_status` varchar(100) DEFAULT NULL,
  `source_id` int(10) unsigned DEFAULT NULL,
  `referrer_id` int(10) unsigned DEFAULT NULL,
  `search_engine_id` int(10) DEFAULT NULL,
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
  `sub_id_11_id` int(10) unsigned DEFAULT NULL,
  `sub_id_12_id` int(10) unsigned DEFAULT NULL,
  `sub_id_13_id` int(10) unsigned DEFAULT NULL,
  `sub_id_14_id` int(10) unsigned DEFAULT NULL,
  `sub_id_15_id` int(10) unsigned DEFAULT NULL,
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
  `revenue` decimal(10,4) DEFAULT 0.0000,
  `cost` decimal(13,6) DEFAULT 0.000000,
  `params` text DEFAULT NULL,
  `is_processed` int(1) unsigned NOT NULL DEFAULT 0,
  `creative_id_id` int(10) unsigned DEFAULT NULL,
  `external_id_id` int(10) unsigned DEFAULT NULL,
  `ad_campaign_id_id` int(10) unsigned DEFAULT NULL,
  `sale_datetime` datetime DEFAULT NULL,
  `x_requested_with_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`conversion_id`),
  UNIQUE KEY `sub_id_tid` (`sub_id`,`tid`),
  KEY `campaign_id` (`campaign_id`),
  KEY `affiliate_network_id` (`affiliate_network_id`),
  KEY `x_requested_with_id` (`x_requested_with_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_country_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `countries` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_cron_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_name` varchar(40) NOT NULL,
  `executed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_name` (`task_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_daily_entries` (
  `entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_value` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT 0,
  `unique_clicks` int(10) unsigned NOT NULL DEFAULT 0,
  `bots` int(10) unsigned NOT NULL DEFAULT 0,
  `mobiles` int(10) unsigned NOT NULL DEFAULT 0,
  `empty_referrers` int(10) unsigned NOT NULL DEFAULT 0,
  `proxies` int(10) unsigned NOT NULL DEFAULT 0,
  `conversions` int(10) unsigned NOT NULL DEFAULT 0,
  `leads` int(10) unsigned NOT NULL DEFAULT 0,
  `sales` int(10) unsigned NOT NULL DEFAULT 0,
  `rejected` int(10) unsigned NOT NULL DEFAULT 0,
  `rebills` int(10) unsigned NOT NULL DEFAULT 0,
  `revenue` decimal(13,4) unsigned NOT NULL DEFAULT 0.0000,
  `cost` decimal(13,4) unsigned NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`entry_id`),
  UNIQUE KEY `date_campaign_name_value` (`date`,`campaign_id`,`field_name`,`field_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_delayed_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data` blob NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_domains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_ssl` tinyint(1) NOT NULL,
  `network_status` varchar(255) DEFAULT NULL,
  `default_campaign_id` int(10) unsigned DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `wildcard` tinyint(1) NOT NULL DEFAULT 0,
  `catch_not_found` int(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `error_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_favourite_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `is_shared` tinyint(1) unsigned DEFAULT 0,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_favourite_streams` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `stream_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_stream_id` (`user_id`,`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `type` varchar(255) DEFAULT 'campaign',
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ips` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `ip` int(10) unsigned NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `stream_id` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ip_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` int(11) unsigned NOT NULL,
  `expires_at` datetime NOT NULL,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(12) unsigned DEFAULT NULL,
  `label_name` varchar(50) NOT NULL,
  `ref_name` varchar(100) NOT NULL,
  `ref_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_name_id` (`campaign_id`,`ref_name`,`ref_id`),
  KEY `label_name` (`label_name`),
  KEY `campaign_id` (`campaign_id`),
  KEY `ref_name` (`ref_name`),
  KEY `ref_id` (`ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_landings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `action_payload` text DEFAULT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `offer_count` int(10) NOT NULL DEFAULT 1,
  `state` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `landing_type` varchar(10) DEFAULT 'external',
  `notes` text DEFAULT NULL,
  `action_options` text DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `url` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_monitoring_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(10) NOT NULL,
  `stream_id` int(10) unsigned NOT NULL,
  `trigger_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  `state` enum('unread','read') NOT NULL DEFAULT 'read',
  PRIMARY KEY (`id`),
  KEY `trigger_id` (`trigger_id`),
  KEY `stream_id` (`stream_id`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `action_payload` text DEFAULT NULL,
  `affiliate_network_id` bigint(20) unsigned DEFAULT NULL,
  `payout_value` decimal(10,4) DEFAULT 0.0000,
  `payout_currency` varchar(3) DEFAULT NULL,
  `payout_type` varchar(10) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `payout_auto` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `payout_upsell` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `country` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `action_options` text DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `offer_type` varchar(10) DEFAULT 'external',
  `url` text DEFAULT NULL,
  `conversion_cap_enabled` int(1) unsigned DEFAULT 0,
  `daily_cap` int(10) unsigned DEFAULT 0,
  `conversion_timezone` varchar(50) DEFAULT 'UTC',
  `alternative_offer_id` int(11) DEFAULT NULL,
  `overcap_reject` int(1) unsigned DEFAULT 0,
  `overcap_s2s_skip` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_ad_campaign_ids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_browsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_browser_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `browser_id_value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `region_id_value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_connection_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_creative_ids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_destinations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_device_models` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_device_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_external_ids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_ips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_isp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_languages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_operators` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_os` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_os_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `os_id_value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_params` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `param_name_value` (`id`,`name`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_referrers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_id_value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_search_engines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_sub_ids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_user_agents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_ref_x_requested_with` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_report_bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `is_shared` tinyint(1) unsigned DEFAULT 0,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `keitaro_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_sales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `stream_id` int(10) unsigned DEFAULT NULL,
  `sub_id` varchar(25) NOT NULL,
  `profit` decimal(10,2) DEFAULT NULL,
  `params` text DEFAULT NULL,
  `datetime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_stream_sub` (`group_id`,`stream_id`,`sub_id`),
  KEY `sub_id` (`sub_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_code` varchar(50) NOT NULL,
  `expires_at` datetime NOT NULL,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visitor_code` (`visitor_code`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_shards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(8) NOT NULL,
  `version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'regular',
  `name` varchar(100) DEFAULT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 1,
  `chance` int(3) NOT NULL DEFAULT 0,
  `redirect_type` varchar(50) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `action_options` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `state` varchar(50) NOT NULL DEFAULT 'active',
  `updated_at` datetime DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `action_payload` mediumtext DEFAULT NULL,
  `schema` varchar(30) DEFAULT NULL,
  `collect_clicks` int(1) unsigned DEFAULT 1,
  `filter_or` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `state` (`state`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_stream_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `mode` varchar(40) NOT NULL,
  `payload` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_stream_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'position',
  `uniq_time` int(11) NOT NULL DEFAULT 24,
  `uniq_check_type` varchar(50) NOT NULL DEFAULT 'ip',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `exit_stream` text DEFAULT NULL,
  `exit_stream_type` varchar(20) NOT NULL DEFAULT 'location',
  `action_for_bots` varchar(50) NOT NULL DEFAULT '404',
  `bot_redirect_url` text DEFAULT NULL,
  `bot_text` text DEFAULT NULL,
  `conversion_tracking_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `disable_tracking_exit` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `advertising_mode` int(1) unsigned NOT NULL DEFAULT 0,
  `position` int(10) unsigned DEFAULT NULL,
  `state` varchar(50) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `key` (`key`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_stream_landing_associations` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(20) unsigned NOT NULL,
  `landing_id` int(20) unsigned NOT NULL,
  `state` varchar(10) NOT NULL,
  `share` int(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stream_landing` (`stream_id`,`landing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_stream_offer_associations` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(20) unsigned NOT NULL,
  `offer_id` int(20) unsigned NOT NULL,
  `state` varchar(10) NOT NULL,
  `share` int(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stream_offer` (`stream_id`,`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_third_party_integration` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `integration` varchar(255) NOT NULL,
  `settings` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_third_party_integration_campaign_associations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `integration_id` int(20) unsigned NOT NULL,
  `campaign_id` int(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `integration_campaign` (`integration_id`,`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_traffic_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `postback_url` text DEFAULT NULL,
  `postback_statuses` varchar(255) DEFAULT '["sale","lead","rejected","rebill"]',
  `template_name` varchar(255) DEFAULT NULL,
  `accept_parameters` tinyint(1) unsigned DEFAULT 1,
  `parameters` text DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `traffic_loss` decimal(4,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_triggers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(10) unsigned NOT NULL,
  `target` varchar(50) NOT NULL,
  `condition` varchar(100) NOT NULL,
  `selected_page` varchar(255) DEFAULT NULL,
  `pattern` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `interval` int(10) unsigned NOT NULL,
  `next_run_at` int(10) unsigned DEFAULT NULL,
  `alternative_urls` text DEFAULT NULL,
  `grab_from_page` varchar(250) DEFAULT NULL,
  `av_settings` text DEFAULT NULL,
  `reverse` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `scan_page` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('ADMIN','USER') NOT NULL DEFAULT 'USER',
  `login` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `permissions` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`login`,`password`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_user_bot_ips` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `min_ip` int(10) unsigned NOT NULL,
  `max_ip` int(10) unsigned NOT NULL,
  `raw_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ips` (`min_ip`,`max_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_user_campaign_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `features` text DEFAULT NULL,
  `metrics` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_campaign_id_idx` (`user_id`,`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_user_password_hashes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_user_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `pref_name` varchar(50) NOT NULL,
  `pref_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_pref_name` (`user_id`,`pref_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `keitaro_visitors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_code` varchar(100) NOT NULL,
  `ip_id` int(10) unsigned NOT NULL,
  `user_agent_id` int(10) unsigned NOT NULL,
  `country_id` int(10) unsigned DEFAULT NULL,
  `region_id` int(10) unsigned DEFAULT NULL,
  `city_id` int(10) unsigned DEFAULT NULL,
  `device_type_id` int(10) unsigned DEFAULT NULL,
  `device_model_id` int(10) unsigned DEFAULT NULL,
  `screen_id` int(10) unsigned DEFAULT NULL,
  `language_id` int(10) unsigned DEFAULT NULL,
  `browser_id` int(10) unsigned DEFAULT NULL,
  `browser_version_id` int(10) unsigned DEFAULT NULL,
  `os_id` int(10) unsigned DEFAULT NULL,
  `os_version_id` int(10) unsigned DEFAULT NULL,
  `connection_type_id` int(10) unsigned DEFAULT NULL,
  `operator_id` int(10) unsigned DEFAULT NULL,
  `isp_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`visitor_code`),
  KEY `user_agent_id` (`user_agent_id`),
  KEY `ip_id` (`ip_id`),
  KEY `country_id` (`country_id`),
  KEY `region_id` (`region_id`),
  KEY `city_id` (`city_id`),
  KEY `device_type_id` (`device_type_id`),
  KEY `device_model_id` (`device_model_id`),
  KEY `operator_id` (`operator_id`),
  KEY `isp_id` (`isp_id`),
  KEY `os_version_id` (`os_version_id`),
  KEY `screen_id` (`screen_id`),
  KEY `language_id` (`language_id`),
  KEY `browser_id` (`browser_id`),
  KEY `os_id` (`os_id`),
  KEY `connection_type_id` (`connection_type_id`),
  KEY `browser_version_id` (`browser_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `name` varchar(255) NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `schema_version` (
  `version` int(10) unsigned NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160909120710_CreateApiKeys');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160909120710_FirstMigration');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160918130517_CreateClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160918135109_CreateConversions2');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160918135327_CreateRawClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160918135512_CreateRefTables');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20160918142331_CreateDailyEntries');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161003163037_AddTypeToStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161003163705_SyncDbSchema');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161004101422_RenameStreamActionFields');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161004102502_ExtractDefaultActions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161005154527_MigrateRedirectDataInStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161007100510_AddCollectClicksToStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161007163321_MigrateBotActionsToForcedActions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161008085616_AddUniqueIndexToSchemaMigrations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161012092340_CreateLanding');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161019223117_GroupType');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161020230734_UserCampaignPermissions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161031233854_CreateAffiliateNetwork');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161104002542_LandingAddState');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161105092911_AddSchemaLandingOfferToStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161105102135_UpdateSchemaInStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161107152857_CreateOffersTable');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161108105014_ChangeOfferCount');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161108142454_CreateStreamLandingAssociations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161108142459_CreateStreamOfferAssociations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161108190417_RenameTimestampsInLandings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161111111151_AddNameToStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161112142644_RenameTimestampsInNetworks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161112142645_RenameTimestampsInOffers');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161114224931_RenameTimestampsInNetworks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161115111649_AddBcryptPassword');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161202133027_AddIsUniqueGlobalToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161202135427_AddLeadSaleRejectedRevenuesToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161205104437_RenameClicksToEvents');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161206231240_AddLpClickDatetime');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161208160631_UpdateIndexesOfEvents');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161208222930_RenameRawClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161210140656_RemoveCountryIdFromRefRegions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161210141020_RemoveCountryREgionFromRefCities');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161212093157_CreateUserPreferences');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161212104538_ExtractPreferences');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161213151819_RenameQueueToDelayedTask');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161214125109_RenameEventQueue');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161219200151_RenameDbDraft');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220075354_AddvisitorIdToEvents');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220121100_CreateVisitors');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220122451_MigrateVisitors');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220125239_CreateRefDeviceTypes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220125637_CreateRefConnectionTypes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220130046_CreateDestinations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220130444_CreateNewRefColumnsToEvents');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161220130650_CreateTrafficSource');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161221065433_SetDestinationDefaultNull');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161221075100_RenameEventsToClicksBack');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161221111103_AddVisitorIdToConversions2');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161221123617_DropParentAttrFromRefs');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161222121216_CreateRefSubIds');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161222132145_AddOperatorIdIspIdToVisitors');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223065716_RenameCodeToVisitorCode');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223102826_MakeDefaultNullInVisitors');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223105843_RemoveVisitorFieldsFromClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223113029_CreateRefSearchEngines');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223122446_CreateDestinations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161223130012_UpdateConversions2');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161224103230_CreateLanguages');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161224103732_RenameSeToSearchEngine');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161229080919_CampaignAddTrafficSource');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20161229081815_FixVisitorIndex');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170102101009_AddTokenToCampaigns');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170103102302_AddExternalIdCreativeId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170104162554_CreateExternalIdsCreativeIds');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170104181011_AddGoalsToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170105063028_AddLandingClickedToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170120204756_AddDefaultsToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170120210133_AddSettingStatsTtl');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170125162047_MakeTokenDefaultNull');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170126164849_SetDefaultPostbackUrlNetworks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170126165727_SetDefaults2sPostbackInSource');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170127143525_CampaignCostAuto');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170130125927_OfferUpsellAuto');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170131131619_AddClickCreativeExternalIdsToConversion');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170202215517_CreateSessions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170203135535_CreateCronStatus');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170204130131_CreateReportBookmarks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170207104307_CreateAccessTables');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170207205026_RenameReportBookmarkToFavouriteReport');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170209110539_MigrateUserRulesToAcl');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170209161316_AddParentCampaignIdToClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170209161527_UpdateIndexesInClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170211203840_CreateAclReports');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170213140641_RenameTokenToParametersInTs');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170213202841_AddAdCampaignId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170213203606_CreateRefAdCampaignIds');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170214143434_RenameCreativeExternalAdCampaign');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170214174804_SyncFieldInConversions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170224142540_AddIndexes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170306112329_CreateAclResource');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170323201853_CampaignBindVisitorLandingOffer');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170324110035_IncreaseActionPayloadSize');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170328155219_AffiliateNetworkOfferParam');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170329080023_IncreaseDeviceTypeValueSize');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170329091946_MakeValuesBinary');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170330135859_MigrateResourceAcl');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170403155739_AddSaleDatetimeToConversions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170406095030_ChangeTypeOfLandingClickedDatetime');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170411135709_FixRejectedClicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170413104235_AllowEmptyIp');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170417101608_UpdateAclEntityNames');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170418191031_MultipleAuthHash');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170421142042_AddClickIndexes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170428194007_AddVisitorIndexes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170501203702_AddDomain');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170502085659_CreateLabels');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170502174539_FixLeadRevenue');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170506193451_ConvertAclTypes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170506215758_FixVisitorIndex');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170509185110_FixGroups');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170511083150_UpdateIndexForLabels');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170513204508_CostDecimals');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170514140937_AddTrafficSourcePostbackType');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170515145610_AddLandingTypeAndMacrosEnabled');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170517120358_AddClickCompositeIndexes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170518095017_AllowEmptyStream');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170519070632_UpdateTrafficSourceStatus');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170521113756_AddStreamFilterOr');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170529074640_AddClickParentSubId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170609192530_CampaignAddDomainId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170610093539_ChangeSchemaForCurl');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170610102246_ChangeSchemaOffersToLandings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170719123810_OfferAddCountryAttribute');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170721113953_RemoveStreamLandingOfferId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170721161927_DeleteBadDomainCampaignRef');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170801222907_AddEntityNotes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170807193137_DomainAddWildCard');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170808192436_IncreaseAdCampaignSize');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170808235201_CreateBranding');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170813111110_DenormalizeAffiliateNetwork');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170816234158_MergeRefLanguage');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20170913132236_CreateStreamItems');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171002103004_AddLandingOfferRedirectType');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171018195705_ExtendExternalId');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171021203304_MoveListToDbAndRedis');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171028114312_AddFolderForLandings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171030121803_SetLandingActionPayloadNullable');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171031182540_FixSessionExpire');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171105205110_CampaignUniquenessDefault');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171105214224_CampaignAddParams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171113102953_AddOfferType');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171113181211_UpdateOffersAndLandersActionTypes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171124202347_RemoveLandingFolder');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171204160540_MigrateOfferCountry');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171213112657_AddSubId15Clicks');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171213112709_AddSubId15Conversions');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171218141002_ReturnUrlsForOffersAndLandings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171218153120_AddLpDir');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20171222190026_DisableExtraParam');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180104120559_AddFavouriteToStreams');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180111152054_MakeActionPayloadDefaultNull');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180118121205_AddIsSidebarEnabled');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180123203946_ReturnV8Urls');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180330131635_AddAffiliateNetworkPullApi');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180401192850_AddXRequestedWith');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180427184110_AddCatchNotFoundOnCampaign');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180706141520_AddDomainNotes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20180913204006_GeoDbToIpV6');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181108132533_UpdateColumnExistence');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181112150624_AddUniquenessUseCookies');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181217122224_ChangeSettingsType');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181226124112_AddTrafficLossToTs');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181226124411_AddTrafficLossToCampaign');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20181227121843_AddCapFieldsToOffers');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190122134734_RemoveEnumFromMode');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190125072639_FixBotStreamNameAndSchema');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190125105401_RemoveAntibot');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190129070615_UpdateOfferTypes');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190223132533_UpdateColumnExistence');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190313102422_ChangeRefSource');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190320060356_Ip2locationToken');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190329121835_AddLpOfferTokenTtl');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190509073714_ConvertLpTokens');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190514145706_ConvertOldCustomFilters');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190618084422_AddOfferOvercapSettings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190622184545_UpdateGeodbSettings');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20190708092604_AddDomainCheckErrorColumn');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20191209155455_CreateThirdPartyIntegration');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20191209163154_CreateThirdPartyIntegrationCampaignAssociations');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20200120101505_UpdateIntegrationsData');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20200204141227_UpdateAppsFlyerIntegration');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20200210064255_RemovePullApi');
INSERT IGNORE INTO schema_migrations(`name`) VALUES('Migration_20200220124125_DisableUseCFCountry');
