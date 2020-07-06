<?php
class Migration_20161221065433_SetDestinationDefaultNull extends Migration 
{
    const DESCRIPTION_RU = 'Изменени events.destination_id default null';

    const DESCRIPTION_EN = 'Change events.destination_id to default null';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}events CHANGE `destination_id` `destination_id` bigint unsigned DEFAULT NULL";
        self::execute($sql);
    }
}