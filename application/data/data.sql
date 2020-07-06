INSERT IGNORE INTO `schema_version` (`version`) VALUES ('102');

INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(1, 'USA and Canada', 'US CA');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(2, 'West Europe',  'GB DE FR IT AT CH ES NL BE DK SE NO PT FI IS IE LI LU MC AD GI GR MT SM VA FO CY');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(3, 'Europe', 'AL GB DE FR IT AT CH ES NL BE DK SE NO PT FI IS IE LI LU MC AD GI GR MT SM VA FO CY BY BA BG HR CZ EE HU LV LT MK MD ME PL RO RS SK SI');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(4, 'exUSSR', 'AM AZ BY EE GE KZ KG LV LT MD RU TJ TM UA UZ');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(5, 'English-Speaking', 'US GB CA AU NZ IE ZA SG JM TT GY BB');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(6, 'German-Speaking', 'AT CH LI LU DK');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(7, 'French-Speaking', 'FR MC LU CD MG CI CM BF NE SN ML BE');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(8, 'Portuguese-Speaking', 'AO BR PT CV GW MZ ST GQ MU');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(9, 'Spanish-Speaking', 'CO ES AR MX VE PE CL EC GT CU DO HN BO SV NI PY CR UY PA GQ');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(10, 'Italian-Speaking', 'CH SM VA MT HR SI');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(11, 'North America', 'AI AG AW BS BB BZ BM VI CA KY CR CU DO SV GL GD GP GT HT HN JM MQ MX MS NL NI PA PR KN LC PM VC TT TC US');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(12, 'USA, Canada and Europe', 'US CA AL GB DE FR IT AT CH ES NL BE DK SE NO PT FI IS IE LI LU MC AD GI GR MT SM VA FO CY BY BA BG HR CZ EE HU LV LT MK MD ME PL RO RS SK SI');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(13, 'English-Speaking and West Europe', 'US GB CA AU NZ IE ZA SG JM TT GY BB DE FR IT AT CH ES NL BE DK SE NO PT FI IS LI LU MC AD GI GR MT SM VA FO CY');
INSERT IGNORE INTO `keitaro_country_profiles` (`id`, `name`, `countries`) VALUES(14, 'English-Speaking and Europe', 'US GB CA AU NZ IE ZA SG JM TT GY BB AL DE FR IT AT CH ES NL BE DK SE NO PT FI IS LI LU MC AD GI GR MT SM VA FO CY BY BA BG HR CZ EE HU LV LT MK MD ME PL RO RS SK SI');


INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('check_bot_ip', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('check_bot_referer', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('check_bot_empty_ua', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('check_bot_ua', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('check_bot_prefetch', '0');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('disable_stats', '0');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('cache_storage', 'auto');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('memcached_server', 'localhost:11211');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('redis_server', '127.0.0.1:6379/1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('av_service', 'avscan');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('operators_service', 'carrierdb');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('geodb', 'ip2location_lite');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('links_style', 'new');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('currency', 'USD');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('id_aliases', 'id, g, group, sid');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('keyword_aliases', 'keyword, utm_term, utm_kwd');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('referrer_aliases', 'referrer, referer');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('se_aliases', '');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('se_referrer_aliases', 'se_referer, se_referrer, seoref');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('source_aliases', 'source, utm_source');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('sub_id_1_aliases', '');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('sub_id_2_aliases', '');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('sub_id_3_aliases', '');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('sub_id_4_aliases', '');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('draft_data_storage', 'file');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('extra_action', 'redirect');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('detect_spam_bots', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('stats_ttl', '256');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('archive_ttl', '60');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('lp_dir', 'lander');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('lp_allow_php', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('force_token_files', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('show_extra_param', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('is_sidebar_enabled', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('avoid_mysql', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('ipdb', '{"0":null,"1":null,"2":null,"3":null,"4":null,"5":null,"6":null,"7":null,"8":null,"country":"ip2location_lite","region":"ip2location_lite","city":"ip2location_lite","city_ru":null,"connection_type":"keitaro_carrier","operator":"keitaro_carrier","bot_type":"keitaro_bot_db2","isp":null,"proxy_type":null}');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('conversions_2_previous_conversion_id_exists', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('conversions_2_sub_id_15_id_exists', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('conversions_2_x_requested_with_id_exists', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('conversions_2_affiliate_network_id_exists', '1');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('clicks_sub_id_15_id_exists', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('clicks_x_requested_with_id_exists', '1');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('clicks_affiliate_network_id_exists', '1');

INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('is_beta_channel', '0');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('s2s_timeout', '5');
INSERT IGNORE INTO `keitaro_settings` (`key`, `value`) VALUES ('lp_offer_token_ttl', '1440');

INSERT IGNORE INTO `keitaro_user_preferences` (`user_id`, `pref_name`, `pref_value`) VALUES ('1', 'timezone', 'UTC');
INSERT IGNORE INTO `keitaro_user_preferences` (`user_id`, `pref_name`, `pref_value`) VALUES ('1', 'language', 'en');
