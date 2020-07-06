<?php
class Migration_20170104162554_CreateExternalIdsCreativeIds extends Migration 
{
    const DESCRIPTION_RU = 'Создание ref_external_ids и ref_creative_ids';

    const DESCRIPTION_EN = 'Create ref_external_ids and ref_creative_ids';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_creative_ids` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(15) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        self::execute($sql);
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_external_ids` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(15) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        self::execute($sql);
    }
}