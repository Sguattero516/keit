<?php
class Migration_20190223132533_UpdateColumnExistence extends Migration
{
    const DESCRIPTION_RU = 'Обновление состояний clicks и conversions';

    const DESCRIPTION_EN = 'Update state of clicks and conversions';

    public static function up()
    {
        $conversionColumns = [
            'previous_conversion_id',
            'sub_id_15_id',
            'x_requested_with_id',
            'affiliate_network_id'
        ];
        $prefix = self::getPrefix();

        foreach ($conversionColumns as $column) {
            $sql = "SHOW COLUMNS FROM `{$prefix}conversions_2` LIKE " . \Core\Db\Db::quote($column);
            $result = self::getDb()->execute($sql)->getArray();
            if (!empty($result)) {
                $sql = "INSERT IGNORE INTO {$prefix}settings (`key`, `value`) values (\"conversions_2_{$column}_exists\", 1)";
                self::execute($sql);
            }
        }

        $clicksColumns = [
            'sub_id_15_id',
            'x_requested_with_id',
            'affiliate_network_id'
        ];
        $prefix = self::getPrefix();

        foreach ($clicksColumns as $column) {
            $sql = "SHOW COLUMNS FROM `{$prefix}clicks` LIKE " . \Core\Db\Db::quote($column);
            $result = self::getDb()->execute($sql)->getArray();
            if (!empty($result)) {
                $sql = "INSERT IGNORE INTO {$prefix}settings (`key`, `value`) values (\"clicks_{$column}_exists\", 1)";
                self::execute($sql);
            }
        }
    }
}