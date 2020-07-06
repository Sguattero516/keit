<?php
class Migration_20160918135512_CreateRefTables extends Migration
{
    const DESCRIPTION_RU = 'Создание таблиц ref_*';

    const DESCRIPTION_EN = 'Create tables ref_*';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'raw_clicks` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `created_at` date DEFAULT NULL,
          `payload` text NOT NULL,
          PRIMARY KEY (`id`),
          KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_browsers` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(100) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_browser_versions` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `browser_id` int(10) unsigned NOT NULL,
          `value` varchar(32) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `browser_id_value` (`browser_id`,`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_cities` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `country_id` int(10) unsigned NOT NULL,
          `region_id` int(10) unsigned NOT NULL,
          `value` varchar(100) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `region_id_value` (`region_id`,`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_countries` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(2) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_device_models` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(100) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_ips` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_isp` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(100) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_keywords` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_operators` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(100) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_os` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(32) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_os_versions` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `os_id` int(10) unsigned NOT NULL,
          `value` varchar(32) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `os_id_value` (`os_id`,`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_params` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(64) NOT NULL,
          `value` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `param_name_value` (`id`,`name`,`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_referrers` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_regions` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `country_id` int(10) unsigned NOT NULL,
          `value` varchar(64) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `country_id_value` (`country_id`,`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_sources` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_user_agents` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}