<?php
class Migration_20161005154527_MigrateRedirectDataInStreams extends Migration 
{
    const DESCRIPTION_RU = 'Копирование streams.url в streams.action_payload, streams.redirect_type в steams.action_type';

    const DESCRIPTION_EN = 'Copy streams.url to streams.action_payload, streams.redirect_type to steams.action_type';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = "UPDATE {$prefix}streams SET action_payload = `url` WHERE action_payload IS NULL";
        self::silentExecute($sql);

        $sql = "UPDATE {$prefix}streams SET action_type = `redirect_type` WHERE action_type IS NULL";
        self::silentExecute($sql);
    }
}