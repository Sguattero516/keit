<?php
class Migration_20170324110035_IncreaseActionPayloadSize extends Migration 
{
    const DESCRIPTION_RU = 'Увеличение размера action_payload в streams';

    const DESCRIPTION_EN = 'Increase action_payload in streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}streams 
          CHANGE COLUMN action_payload action_payload MEDIUMTEXT default null";
        self::execute($sql);
    }
}