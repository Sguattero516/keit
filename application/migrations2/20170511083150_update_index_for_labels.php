<?php
class Migration_20170511083150_UpdateIndexForLabels extends Migration 
{
    const DESCRIPTION_RU = 'Обновление индексов в labels';

    const DESCRIPTION_EN = 'Update index for labels table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}labels DROP INDEX campaign_label_ref,
        ADD INDEX campaign_id(campaign_id),
        ADD INDEX ref_name(ref_name),
        ADD INDEX ref_id(ref_id),
        ADD UNIQUE campaign_name_id(campaign_id, ref_name, ref_id)
        ";
        self::execute($sql);
    }
}