<?php
class Migration_1 extends Migration {

    const DESCRIPTION_RU = 'Проверка функционала миграций';

    const DESCRIPTION_EN = 'Test migration runner';

    public static function up()
    {
        $sql = "INSERT IGNORE INTO " . self::getDb()->getPrefix() . "settings (`key`, `value`) VALUES ('migration_system_works', 'yes')";
        self::getDb()->execute($sql);
    }
}
