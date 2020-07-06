<?php
class Migration_20170104181011_AddGoalsToClicks extends Migration 
{
    const DESCRIPTION_RU = 'Добавление goal_1..4 и goal_1..4_datetime в clicks';

    const DESCRIPTION_EN = 'Add goal_1..4 and goal_1..4_datetime to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
        ADD COLUMN goal_1 tinyint(1) unsigned DEFAULT 0,
        ADD COLUMN goal_1_datetime datetime DEFAULT NULL,
        ADD COLUMN goal_2 tinyint(1) unsigned DEFAULT 0,
        ADD COLUMN goal_2_datetime datetime DEFAULT NULL,
        ADD COLUMN goal_3 tinyint(1) unsigned DEFAULT 0,
        ADD COLUMN goal_3_datetime datetime DEFAULT NULL,
        ADD COLUMN goal_4 tinyint(1) unsigned DEFAULT 0,
        ADD COLUMN goal_4_datetime datetime DEFAULT NULL
        ";
        self::execute($sql);
    }
}