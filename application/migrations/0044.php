<?php


class Migration_44 extends Migration {

    const DESCRIPTION_RU = 'Добавление поля state в monitoring_history';

    const DESCRIPTION_EN = 'Add column "state" to monitoring_history';

    public static function up()
    {
        $sql = 'ALTER TABLE `' .  self::getDb()->getPrefix() . 'monitoring_history`
        ADD `state` ENUM(\'unread\', \'read\' ) NOT NULL DEFAULT \'read\',
        ADD INDEX (state)';
        self::execute($sql);
    }
}