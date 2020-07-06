<?php
class Migration_20160918135327_CreateRawClicks extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы raw_clicks';

    const DESCRIPTION_EN = 'Create table raw_clicks';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'raw_clicks` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `created_at` date DEFAULT NULL,
          `payload` text NOT NULL,
          PRIMARY KEY (`id`),
          KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

        self::execute($sql);
    }
}