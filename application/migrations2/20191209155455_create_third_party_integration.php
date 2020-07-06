<?php
class Migration_20191209155455_CreateThirdPartyIntegration extends Migration
{
    const DESCRIPTION_RU = 'Создание таблицы third_party_integration';

    const DESCRIPTION_EN = 'Create table third_party_integration';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'third_party_integration` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `integration` varchar(255) NOT NULL,
          `settings` text DEFAULT NULL,
          `created_at` DATETIME NOT NULL,
          `updated_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}