<?php
class Migration_20171105205110_CampaignUniquenessDefault extends Migration 
{
    const DESCRIPTION_RU = 'Смена значения по умолчанию для uniqueness_method у campaigns';

    const DESCRIPTION_EN = 'Change default value for uniqueness_method in campaigns';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns MODIFY `uniqueness_method` VARCHAR(20) NOT NULL DEFAULT 'ip_ua'";
        self::execute($sql);
    }
}