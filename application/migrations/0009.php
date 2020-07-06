<?php


class Migration_9 extends Migration {

    const DESCRIPTION_RU = 'Настройки для управления кэшем';

    const DESCRIPTION_EN = 'Add settings for cache control';

    public static function up()
    {
        $db = self::getDb();
        $sqls = array(
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('cache_storage', 'auto');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('memcached_server', 'localhost:11211');",
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('redis_server', '127.0.0.1:6379/1');",
        );

        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }
}