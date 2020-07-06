<?php
class Migration_20170513204508_CostDecimals extends Migration 
{
    const DESCRIPTION_RU = 'Увеличение количества символов conversions.cost и clicks.cost';

    const DESCRIPTION_EN = 'Increase symbol count conversions.cost and clicks.cost';

    public static function up($rerun = false)
    {
        $prefix = self::getPrefix();
        $count = self::getDb()->getOne("SELECT count(*) from {$prefix}clicks");
        if ($count < 1000000 || $rerun) {
            $prefix = self::getDb()->getPrefix();
            $sql = "ALTER TABLE `{$prefix}clicks`
                modify column cost decimal(13,6) NOT NULL DEFAULT '0.000000';";
            self::silentExecute($sql);
            $sql = "ALTER TABLE `{$prefix}conversions_2`
                modify column cost decimal(13,6) DEFAULT '0.000000';";
            self::silentExecute($sql);
        }
    }
}