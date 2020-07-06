<?php
class Migration_20181226124112_AddTrafficLossToTs extends Migration 
{
    const DESCRIPTION_RU = 'Добавление traffic_loss в traffic_sources';

    const DESCRIPTION_EN = 'Add traffic_loss to traffic_sources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}traffic_sources ADD COLUMN traffic_loss decimal(4,2) DEFAULT 0";
        self::silentExecute($sql);
    }
}