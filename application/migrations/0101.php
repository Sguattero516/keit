<?php
class Migration_101 extends Migration {

    const DESCRIPTION_RU = 'Переход на новую версию миграций';

    const DESCRIPTION_EN = 'Move to new migrations version';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS schema_migrations (
            `name` VARCHAR( 255 ) NOT NULL,
            INDEX ( `name` )
            ) ENGINE = INNODB;";

        self::execute($sql);
    }
}