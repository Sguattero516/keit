<?php
class Migration_83 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы ip_sessions';

    const DESCRIPTION_EN = 'Create table ip_sessions';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "CREATE TABLE `{$prefix}ip_sessions` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `ip` int(11) UNSIGNED NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `payload` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ip` (`ip`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
        ";

        self::execute($sql);
    }
}