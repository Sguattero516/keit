<?php
class Migration_20170323201853_CampaignBindVisitorLandingOffer extends Migration 
{
    const DESCRIPTION_RU = 'Изменение типа bind_visitors в campaigns';

    const DESCRIPTION_EN = 'Change type of bind_visitors in campaigns';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
            ALTER TABLE `{$prefix}campaigns`
            ADD COLUMN `bind_visitors_new` varchar(255) NULL DEFAULT NULL AFTER `bind_visitors`;
            
            UPDATE `{$prefix}campaigns` SET `bind_visitors_new` = 's' WHERE `bind_visitors` = 1;
            
            ALTER TABLE `{$prefix}campaigns` 
            DROP COLUMN `bind_visitors`;
            
            ALTER TABLE `{$prefix}campaigns`  
            CHANGE COLUMN `bind_visitors_new` `bind_visitors` VARCHAR(255) NULL DEFAULT NULL;";
        self::execute($sql);
    }
}