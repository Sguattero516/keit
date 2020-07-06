<?php
class Migration_20180427184110_AddCatchNotFoundOnCampaign extends Migration 
{
    const DESCRIPTION_RU = 'Добавление catch_not_found в domains';

    const DESCRIPTION_EN = 'Add catch_not_found to domains';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}domains ADD COLUMN catch_not_found INT(1) DEFAULT 1";
        self::silentExecute($sql);
    }
}