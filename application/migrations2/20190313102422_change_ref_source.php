<?php
class Migration_20190313102422_ChangeRefSource extends Migration
{
    const DESCRIPTION_RU = 'Увеличение размера колонки ref_sources.value';

    const DESCRIPTION_EN = 'Extend ref_sources.value';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE `{$prefix}ref_sources` CHANGE COLUMN `value` `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;";
        self::execute($sql);
    }
}