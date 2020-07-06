<?php

class Migration_20170127143525_CampaignCostAuto extends Migration 
{
    const DESCRIPTION_RU = 'Добавление поля cost_auto в таблицу campaigns';

    const DESCRIPTION_EN = 'Add field cost_auto to campaigns table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns ADD COLUMN cost_auto TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}campaigns SET cost_auto = 1 WHERE cost_value = CAST(0.0000 AS DECIMAL(10,4));";
        self::execute($sql);
    }
}