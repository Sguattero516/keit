<?php
class Migration_20161202135427_AddLeadSaleRejectedRevenuesToClicks extends Migration 
{
    const DESCRIPTION_RU = 'Добавление lead_revenue, sale_revenue, rejected_revenue в clicks';

    const DESCRIPTION_EN = 'Add lead_revenue, sale_revenue, rejected_revenue to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();

        $sql = "ALTER TABLE {$prefix}clicks 
            ADD COLUMN rejected_revenue DECIMAL(13,4) NOT NULL DEFAULT '0.0000' AFTER extra_param_10,
            CHANGE COLUMN revenue sale_revenue DECIMAL(13,4) NOT NULL DEFAULT '0.0000',
            ADD COLUMN lead_revenue DECIMAL(13,4) NOT NULL DEFAULT '0.0000' AFTER extra_param_10
        ";
        self::execute($sql);

    }
}