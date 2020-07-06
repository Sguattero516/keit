<?php
class Migration_20161229081815_FixVisitorIndex extends Migration 
{
    const DESCRIPTION_RU = 'Правка os_version_id индекса';

    const DESCRIPTION_EN = 'Fix os_version_id index';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}visitors 
          DROP INDEX os_version_id,
          ADD INDEX os_version_id (os_version_id)";
        self::execute($sql);
    }
}