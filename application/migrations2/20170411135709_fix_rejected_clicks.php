<?php
class Migration_20170411135709_FixRejectedClicks extends Migration 
{
    const DESCRIPTION_RU = 'Исправление кликов в состоянии "Отклонено"';

    const DESCRIPTION_EN = 'Fix clicks in state "Rejected"';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "SELECT click_id, sub_id FROM {$prefix}clicks WHERE is_rejected = 1";
        foreach (self::execute($sql) as $clickRow) {
            $conversionRow = static::getConversion($clickRow['sub_id']);

            if ($conversionRow['status'] == 'lead') {
                $sql = "UPDATE {$prefix}clicks SET is_rejected = 0, is_lead = 1, 
                  rejected_revenue = 0, lead_revenue = '{$conversionRow['revenue']}'
                  WHERE click_id = {$clickRow['click_id']}";
                self::execute($sql);
            }
        }
    }

    public static function getConversion($subId)
    {
        $prefix = self::getPrefix();
        $sql = "SELECT * FROM {$prefix}conversions_2 WHERE sub_id = '{$subId}'";
        return self::getDb()->getRow($sql);
    }
}