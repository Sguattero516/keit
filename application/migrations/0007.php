<?php


class Migration_7 extends Migration {

    const DESCRIPTION_RU = 'Добавление настроек статистики';

    const DESCRIPTION_EN = 'Add new settings for report';

    public static function up()
    {
        $db = self::getDb();
        $sqls = array(
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('detailed_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('hours_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('daily_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('countries_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('keywords_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('sources_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('se_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('referers_statistic_enabled', '1');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('streams_statistic_enabled', '1');"
        );

        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }
}