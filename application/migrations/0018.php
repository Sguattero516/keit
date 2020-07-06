<?php


class Migration_18 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы sales';

    const DESCRIPTION_EN = 'Create table sales';

    public static function up()
    {
        if (!self::tryToCreate('InnoDB')) {
            self::tryToCreate('MyISAM');
        }
    }

    public static function tryToCreate($engine)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . self::getDb()->getPrefix() . 'sales` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `group_id` int(11) NOT NULL,
          `stream_id` int(10) unsigned DEFAULT NULL,
          `sub_id` varchar(25) NOT NULL,
          `profit` decimal(10,2) DEFAULT NULL,
          `params` text,
          `datetime` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `group_stream_sub` (`group_id`,`stream_id`,`sub_id`),
          KEY `sub_id` (`sub_id`)
        ) ENGINE='.$engine.'  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
        try {
            self::execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }
}