<?php
class Migration_20180111152054_MakeActionPayloadDefaultNull extends Migration 
{
    const DESCRIPTION_RU = 'Выставление default null для action_payload';

    const DESCRIPTION_EN = 'Make default null for action_payload';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}offers CHANGE COLUMN action_payload action_payload text DEFAULT NULL";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}landings CHANGE COLUMN action_payload action_payload text DEFAULT NULL";
        self::execute($sql);
    }
}