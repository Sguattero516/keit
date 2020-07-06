<?php
class Migration_20171018195705_ExtendExternalId extends Migration 
{
    const DESCRIPTION_RU = 'Увеличение длинны external_id таблицы ref_external_ids';
    
    const DESCRIPTION_EN = 'Extend external_id length in ref_external_ids table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}ref_external_ids
                DROP INDEX value,
                MODIFY value VARCHAR(65535) NOT NULL COLLATE 'utf8_bin',
                ADD INDEX (value(255))";
        self::execute($sql);    
    } 
}