<?php
class Migration_102 extends Migration {

    const DESCRIPTION_RU = 'Увеличение размера conversions.sub_id';

    const DESCRIPTION_EN = 'Increase size conversions.sub_id';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions CHANGE COLUMN `sub_id` `sub_id` VARCHAR(30)  NOT NULL";
        self::execute($sql);
    }
}