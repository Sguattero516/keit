<?php


class Migration_4 extends Migration {

    const DESCRIPTION_RU = 'Смена индекса ExitStream на 0';

    const DESCRIPTION_EN = 'ExitStream index change to 0';

    public static function up()
    {
        $db = self::getDb();
        $sql = "UPDATE IGNORE `{$db->getPrefix()}ips` SET `stream_id` = 0 WHERE stream_id = -1";
        $db->execute($sql);
    }
}
