<?php
class Migration_20161003163705_SyncDbSchema extends Migration 
{
    const DESCRIPTION_RU = 'Приведение схемы к заданному формату';

    const DESCRIPTION_EN = 'Convert schema to specified structure';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sqls = [
            "ALTER TABLE {$prefix}campaigns MODIFY `cost_type` varchar(10) DEFAULT 'CPV'",
            "ALTER TABLE {$prefix}conversions MODIFY `revenue` decimal(10,4) DEFAULT '0.0000'",
            "ALTER TABLE {$prefix}conversions MODIFY `cost` decimal(10,4) DEFAULT '0.0000'",
            //"ALTER TABLE {$prefix}index_browsers MODIFY `conversions` int(11) NOT NULL DEFAULT '0'"
            //"ALTER TABLE {$prefix}index_campaigns MODIFY `conversions` int(11) NOT NULL DEFAULT '0'"
            "ALTER TABLE {$prefix}queue MODIFY `datetime` datetime DEFAULT NULL",
            "ALTER TABLE {$prefix}ip_sessions MODIFY `ip` int(11) unsigned NOT NULL",
            "ALTER TABLE {$prefix}streams MODIFY `ip` int(11) unsigned NOT NULL",
            "ALTER TABLE {$prefix}streams DROP INDEX chance",
            "ALTER TABLE {$prefix}streams DROP INDEX group_id",
        ];

        foreach ($sqls as $sql) {
            self::silentExecute($sql);
        }
    }
}