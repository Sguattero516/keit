<?php
class Migration_20170502174539_FixLeadRevenue extends Migration 
{
    const DESCRIPTION_RU = 'Обновление ошибочных доходов по лидам';

    const DESCRIPTION_EN = 'Fixed incorrect lead revenues';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}clicks  SET lead_revenue = sale_revenue, sale_revenue = 0
            WHERE is_lead = 1 AND is_sale = 0 AND sale_revenue > 0
        ";
        self::execute($sql);
    }
}