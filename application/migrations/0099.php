<?php

class Migration_99 extends Migration {

    const DESCRIPTION_RU = 'Увеличение размера значений cost и revenue в conversions';

    const DESCRIPTION_EN = 'Increase size of cost and revenue in conversions';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
        ALTER TABLE `{$prefix}conversions` 
        CHANGE `cost` `cost` decimal(10,4) DEFAULT '0.0000',
        CHANGE `revenue` `revenue` decimal(10,4) DEFAULT '0.0000'
        ";
        self::execute($sql);
    }
}