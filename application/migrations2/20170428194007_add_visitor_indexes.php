<?php
class Migration_20170428194007_AddVisitorIndexes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление индексов в visitors';

    const DESCRIPTION_EN = 'Add indexes to visitors';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}visitors 
          ADD INDEX screen_id(screen_id),
          ADD INDEX language_id(language_id),
          ADD INDEX browser_id(browser_id),
          ADD INDEX os_id(os_id),
          ADD INDEX connection_type_id(connection_type_id)
          ";
        self::execute($sql);
    }
}