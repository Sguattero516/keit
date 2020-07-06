<?php
class Migration_20161104002542_LandingAddState extends Migration 
{
    const DESCRIPTION_RU = 'Добавление полей state, create_datetime, update_datetime к таблице landings';

    const DESCRIPTION_EN = 'Adding fields state, create_datetime, update_datetime to landings table';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}landings 
                ADD COLUMN `state` VARCHAR(255),
                ADD COLUMN `create_datetime` DATETIME NOT NULL,
                ADD COLUMN `update_datetime` DATETIME NOT NULL";
        self::silentExecute($sql);

        $sql = "UPDATE {$prefix}landings SET state = 'active', create_datetime = NOW(), update_datetime = NOW()";
        self::execute($sql);
    }
}