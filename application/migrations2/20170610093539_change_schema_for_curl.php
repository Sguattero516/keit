<?php
class Migration_20170610093539_ChangeSchemaForCurl extends Migration 
{
    const DESCRIPTION_RU = 'Смена схемы для curl, iframe, frame';

    const DESCRIPTION_EN = 'Change scheme for curl, iframe, frame';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}streams SET `schema` = 'redirect' WHERE action_type IN ('curl', 'frame', 'iframe', 'frameset')";
        self::execute($sql);
    }
}