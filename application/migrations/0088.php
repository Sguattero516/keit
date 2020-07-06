<?php
class Migration_88 extends Migration {

    const DESCRIPTION_RU = 'Добавление group_id в campaigns';

    const DESCRIPTION_EN = 'Add group_id to campaigns';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
            ALTER TABLE `{$prefix}campaigns` 
            ADD `group_id` INT UNSIGNED DEFAULT NULL ,
            ADD INDEX ( group_id )
        ";

        self::execute($sql);
    }
}