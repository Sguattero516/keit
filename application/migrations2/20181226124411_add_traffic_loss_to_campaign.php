<?php
class Migration_20181226124411_AddTrafficLossToCampaign extends Migration 
{
    const DESCRIPTION_RU = 'Добавление traffic_loss в campaigns';

    const DESCRIPTION_EN = 'Add traffic_loss to campaigns';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns ADD COLUMN traffic_loss decimal(4,2) DEFAULT 0";
        self::silentExecute($sql);
    }
}