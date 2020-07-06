<?php
class Migration_82 extends Migration {

    const DESCRIPTION_RU = 'Добавление user_agent в conversions';

    const DESCRIPTION_EN = 'Added user_agent to conversions';

    public static function up()
    {
        $prefix =  self::getDb()->getPrefix();
        $sql = "ALTER TABLE `{$prefix}conversions` ADD `user_agent` TEXT NULL AFTER `city`";
        self::execute($sql);
    }
}