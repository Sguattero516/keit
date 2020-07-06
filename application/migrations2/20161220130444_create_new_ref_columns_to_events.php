<?php
class Migration_20161220130444_CreateNewRefColumnsToEvents extends Migration 
{
    const DESCRIPTION_RU = 'Добавление destination_id в events';

    const DESCRIPTION_EN = 'Add destination_id to events';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}events ADD COLUMN destination_id bigint unsigned NOT NULL AFTER destination";
        self::getDb()->execute($sql);

        $sql = "INSERT IGNORE {$prefix}click_destinations (id, destination) values (1, 'removed')";
        self::getDb()->execute($sql);

        $sql = "UPDATE {$prefix}events SET destination_id = 1 WHERE destination_id = 0";
        self::execute($sql);
    }
}