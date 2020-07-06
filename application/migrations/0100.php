<?php
class Migration_100 extends Migration {

    const DESCRIPTION_RU = 'Добавление action_options в keitaro_streams';

    const DESCRIPTION_EN = 'Add action_options to keitaro_streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
            ALTER TABLE `{$prefix}streams`
            ADD  `action_options` TEXT NULL DEFAULT NULL 
            AFTER  `url`
        ";
        self::silentExecute($sql);
    }
}