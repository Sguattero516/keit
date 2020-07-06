<?php


class Migration_5 extends Migration {

    const DESCRIPTION_RU = 'Default NULL для operator_filter_value';

    const DESCRIPTION_EN = 'Null for operator_filter_value';

    public static function up()
    {
        $db = self::getDb();
        $sql = "ALTER TABLE `{$db->getPrefix()}streams` CHANGE `operator_filter_value` `operator_filter_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
        $db->execute($sql);
    }
}
