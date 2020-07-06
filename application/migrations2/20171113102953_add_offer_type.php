<?php
class Migration_20171113102953_AddOfferType extends Migration
{
    const DESCRIPTION_RU = 'Добавление offer_type';

    const DESCRIPTION_EN = 'Add offer_type';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}offers ADD COLUMN offer_type varchar(10) default 'external'";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}offers DROP COLUMN use_stream_action";
        self::execute($sql);
    }
}