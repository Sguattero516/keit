<?php

class Migration_41 extends Migration {

    const DESCRIPTION_RU = 'Установка streams.url and streams.comments в DEFAULT NULL';

    const DESCRIPTION_EN = 'Set streams.url and streams.comments to DEFAULT NULL ';

    public static function up()
    {
        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'streams
            CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
        self::execute($sql);

        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'streams
            CHANGE `comments` `comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
        self::execute($sql);
    }
}

