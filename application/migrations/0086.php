<?php
class Migration_86 extends Migration {

    const DESCRIPTION_RU = 'Добавление cost_type, cost_value, cost_currency в campaigns';

    const DESCRIPTION_EN = 'Add cost_type, cost_value, cost_currency to campaigns';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE `{$prefix}campaigns`
                ADD COLUMN `cost_type` varchar(10) DEFAULT 'CPV',
                ADD COLUMN `cost_value` decimal(10,4) DEFAULT '0.0000',
                ADD COLUMN `cost_currency` varchar(3) DEFAULT NULL";

        self::execute($sql);
    }
}