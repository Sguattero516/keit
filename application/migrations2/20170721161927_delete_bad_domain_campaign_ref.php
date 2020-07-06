<?php
class Migration_20170721161927_DeleteBadDomainCampaignRef extends Migration 
{
    const DESCRIPTION_RU = 'Сброс ссылок на удаленные кампании в domains';

    const DESCRIPTION_EN = 'Reset refs to deleted campaigns in domains';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}domains 
                SET `default_campaign_id` = NULL 
                WHERE default_campaign_id not in (
                    SELECT id
                    FROM {$prefix}campaigns
                );";
        self::execute($sql);
    }
}