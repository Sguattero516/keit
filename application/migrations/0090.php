<?php
class Migration_90 extends Migration {

    const DESCRIPTION_RU = 'Добавление bind_visitors в campaigns';

    const DESCRIPTION_EN = 'Add bind_visitors to campaigns';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
            ALTER TABLE `{$prefix}campaigns`
            ADD `bind_visitors` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'
        ";
        self::execute($sql);
    }
}