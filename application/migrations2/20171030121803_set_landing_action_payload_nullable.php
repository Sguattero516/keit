<?php
class Migration_20171030121803_SetLandingActionPayloadNullable extends Migration
{
    const DESCRIPTION_RU = 'Default null для landings.action_payload';

    const DESCRIPTION_EN = 'Default null для landings.action_payload';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings CHANGE COLUMN action_payload action_payload text DEFAULT NULL,
            CHANGE COLUMN action_options action_options text DEFAULT NULL";
        self::execute($sql);
    }
}