<?php
class Migration_20191209163154_CreateThirdPartyIntegrationCampaignAssociations extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы third_party_integration_campaign_associations';

    const DESCRIPTION_EN = 'Create table third_party_integration_campaign_associations';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'third_party_integration_campaign_associations` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `integration_id` int(20) unsigned NOT NULL,
          `campaign_id` int(20) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `integration_campaign` (`integration_id`,`campaign_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
    }
}