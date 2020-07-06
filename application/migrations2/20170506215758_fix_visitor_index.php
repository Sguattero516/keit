<?php
class Migration_20170506215758_FixVisitorIndex extends Migration 
{
    const DESCRIPTION_RU = 'Правка индекса для browser_version_id в visitors';

    const DESCRIPTION_EN = 'Fix index for browser_version_id in visitors';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE `{$prefix}visitors` DROP INDEX  `browser_version_id`, 
            ADD INDEX browser_version_id (browser_version_id)";
        self::execute($sql);
    }
}