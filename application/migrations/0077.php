<?php
class Migration_77 extends Migration {

    const DESCRIPTION_RU = 'Замена действия "group" на "campaign"';

    const DESCRIPTION_EN = 'Replace action "group" to "campaign"';

    public static function up()
    {
        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'campaigns SET action_type = "campaign" WHERE id IN
            (SELECT id FROM ' . self::getDb()->getPrefix() . 'stream_groups WHERE action_type = "group")';
        self::silentExecute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'streams SET redirect_type = "campaign" WHERE redirect_type = "group"';
        self::execute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'campaigns SET action_type = "http" WHERE id IN
            (SELECT id FROM ' . self::getDb()->getPrefix() . 'stream_groups WHERE action_type = "location")';
        self::silentExecute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'streams SET redirect_type = "http" WHERE redirect_type = "location"';
        self::execute($sql);

    }
}