<?php


class Migration_3 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки uniq_filter_storage';

    const DESCRIPTION_EN = 'Add column uniq_filter_storage';

    public static function up()
    {
        $db = self::getDb();
        $sql = "ALTER TABLE `{$db->getPrefix()}stream_groups` CHANGE `uniq_time` `uniq_time` INT NOT NULL DEFAULT '24'";
        $db->execute($sql);

        $sql = "UPDATE `{$db->getPrefix()}stream_groups` SET uniq_time = 24 WHERE uniq_time = 0";
        $db->execute($sql);

        $sql = "ALTER TABLE `{$db->getPrefix()}streams` ADD `unique_filter` ENUM( '', 'allow') NULL DEFAULT NULL AFTER `uniq_filter`";
        $db->execute($sql);

        $sql = "ALTER TABLE `{$db->getPrefix()}streams` ADD `unique_filter_storage` ENUM( 'cookies', 'ip' ) NOT NULL DEFAULT 'ip' AFTER `uniq_filter`";
        $db->execute($sql);

        $sql = "SELECT * FROM `{$db->getPrefix()}stream_groups` WHERE uniq_check_type = 'cookie'";
        foreach($db->execute($sql) as $row) {
            $sql = "UPDATE `{$db->getPrefix()}streams` SET `unique_filter_storage` = 'cookies' WHERE group_id = {$row['id']}";
            $db->execute($sql);
        }


        $sql = "ALTER TABLE `{$db->getPrefix()}streams` CHANGE `uniq_filter` `unique_filter_scope` ENUM( '', 'enable_stream', 'enable_group', 'enable_global' )  NULL DEFAULT NULL";
        $db->execute($sql);

        $sql = "UPDATE `{$db->getPrefix()}streams` SET `unique_filter` = 'allow'  WHERE unique_filter_scope <> '' AND unique_filter_scope IS NOT NULL";
        $db->execute($sql);
    }
}
