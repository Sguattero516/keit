<?php
class Migration_20170609192530_CampaignAddDomainId extends Migration 
{
    const DESCRIPTION_RU = 'Добавление domain_id к campaigns';

    const DESCRIPTION_EN = 'Add domain_id to campaigns';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns 
                ADD domain_id INT(10) UNSIGNED NULL";
        self::silentExecute($sql);
    }
}