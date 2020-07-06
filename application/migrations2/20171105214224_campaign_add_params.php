<?php
class Migration_20171105214224_CampaignAddParams extends Migration 
{
    const DESCRIPTION_RU = 'Добавление parameters к campaigns';

    const DESCRIPTION_EN = 'Add parameters to campaigns';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns ADD COLUMN parameters TEXT NULL";
        self::silentExecute($sql);
        
        $sql = "update {$prefix}campaigns c
        inner join {$prefix}traffic_sources ts on
            c.traffic_source_id = ts.id
        set c.parameters = ts.parameters
        where c.parameters is NULL and ts.parameters is NOT NULL";
        self::execute($sql);
    }
}