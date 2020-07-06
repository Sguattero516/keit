<?php
class Migration_20161224103230_CreateLanguages extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы ref_languages';

    const DESCRIPTION_EN = 'Create table ref_languages';

    public static function up()
    {
        $prefix = self::getPrefix();

        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_languages` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(15) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

        self::execute($sql);
    }
}