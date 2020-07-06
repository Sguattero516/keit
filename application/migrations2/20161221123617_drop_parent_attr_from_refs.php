<?php
class Migration_20161221123617_DropParentAttrFromRefs extends Migration 
{
    const DESCRIPTION_RU = 'Удаление лишний колонок из ref_os_version, ref_bro';

    const DESCRIPTION_EN = 'Remove excess columns from ref-tables';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}ref_browser_versions DROP KEY `browser_id_value`";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}ref_browser_versions DROP COLUMN browser_id";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}ref_browser_versions ADD UNIQUE value(value)";
        self::silentExecute($sql);
        
    
        $sql = "ALTER TABLE {$prefix}ref_os_versions DROP KEY `os_id_value`";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}ref_os_versions DROP COLUMN os_id";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}ref_os_versions ADD UNIQUE value(value)";
        self::silentExecute($sql);
    }
}