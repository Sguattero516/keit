<?php
class Migration_47 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы campaigns';

    const DESCRIPTION_EN = 'Create table campaigns';

    public static function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getDb()->getPrefix() . "campaigns` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `alias` varchar(50) DEFAULT NULL,
          `name` varchar(200) DEFAULT NULL,
          `type` varchar(20) NOT NULL DEFAULT 'position',
          `cookies_ttl` int(11) NOT NULL DEFAULT '24',
          `action_type` varchar(20) DEFAULT NULL,
          `action_payload` text,
          `action_for_bots` varchar(50) NOT NULL DEFAULT '404',
          `bot_redirect_url` text,
          `bot_text` text,
          `mode` varchar(50) NOT NULL DEFAULT 'general',
          `action_tracking_disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `position` int(10) unsigned DEFAULT NULL,
          `state` varchar(50) NOT NULL DEFAULT 'active',
          PRIMARY KEY (`id`),
          KEY `alias` (`alias`),
          KEY `state` (`state`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        self::execute($sql);

    }
}