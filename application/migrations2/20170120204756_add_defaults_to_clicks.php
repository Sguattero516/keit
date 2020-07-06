<?php
class Migration_20170120204756_AddDefaultsToClicks extends Migration 
{
    const DESCRIPTION_RU = 'Обвноние булевых колонок в clicks';

    const DESCRIPTION_EN = 'Update boolean columns in clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $fields = ['is_unique_stream', 'is_unique_campaign', 'is_lead', 'is_sale', 'is_rejected', 'is_bot', 'is_using_proxy', 'is_empty_referrer'];
        $sql = [];
        foreach ($fields as $field) {
            $sql[] =  "CHANGE COLUMN {$field} {$field} tinyint(1) unsigned DEFAULT 0";
        }
        $sql = "ALTER TABLE {$prefix}clicks " . implode(',' , $sql);
        self::execute($sql);
    }
}