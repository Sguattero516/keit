<?php
class Migration_84 extends Migration {

    const DESCRIPTION_RU = 'Добавление uniqueness_method to campaigns';

    const DESCRIPTION_EN = 'Add uniqueness_method to campaigns';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE `{$prefix}campaigns` ADD `uniqueness_method` VARCHAR( 20 ) NOT NULL DEFAULT 'cookie' AFTER `type` ";

        self::execute($sql);
    }
}