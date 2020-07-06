<?php


class Migration_42 extends Migration {

    const DESCRIPTION_RU = 'Добавление state в streams и stream_groups';

    const DESCRIPTION_EN = 'Add state to streams and stream_groups';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'streams
            ADD `state` VARCHAR( 50 ) NOT NULL DEFAULT \'active\', ADD INDEX ( state )';
        self::execute($sql);

        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'stream_groups
            ADD `state` VARCHAR( 50 ) NOT NULL DEFAULT \'active\', ADD INDEX ( state )';
        self::execute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'streams SET state = "disabled" WHERE status = 0';
        self::silentExecute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'stream_groups SET state = "disabled" WHERE status = 0';
        self::silentExecute($sql);

        $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'stream_groups` CHANGE `status` `status` TINYINT( 1 ) NOT NULL DEFAULT \'1\'';
        self::silentExecute($sql);
    }
}
