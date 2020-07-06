<?php
class Migration_20180123203946_ReturnV8Urls extends Migration 
{
    const DESCRIPTION_RU = 'Восстановление урлов лендингов и офферов формата v8';

    const DESCRIPTION_EN = 'Restore landing and offer url in v8 format';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}landings SET url = action_payload WHERE url is NULL or url = ''";
        self::silentExecute($sql);

        $sql = "UPDATE {$prefix}offers SET url = action_payload WHERE url is NULL or url = ''";
        self::silentExecute($sql);
    }
}