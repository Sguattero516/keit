<?php
class Migration_20161004101422_RenameStreamActionFields extends Migration
{
    const DESCRIPTION_RU = 'Создание колонок у streams action_type и action_payload';

    const DESCRIPTION_EN = 'Create columns action_type and action_payload in table streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}streams  ADD COLUMN `action_type` VARCHAR( 50 ) DEFAULT NULL";
        self::silentExecute($sql);

        $sql = "ALTER TABLE {$prefix}streams  ADD COLUMN `action_payload` TEXT DEFAULT NULL";
        self::silentExecute($sql);


        $sql = "ALTER TABLE {$prefix}streams  CHANGE `redirect_type` `redirect_type` VARCHAR( 50 ) DEFAULT NULL";
        self::silentExecute($sql);

        $sql = "ALTER TABLE {$prefix}streams  CHANGE  `url` `url` TEXT DEFAULT NULL";
        self::silentExecute($sql);
    }
}