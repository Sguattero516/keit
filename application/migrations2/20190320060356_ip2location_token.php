<?php
class Migration_20190320060356_Ip2locationToken extends Migration
{
    const DESCRIPTION_RU = 'Разделение ключей для ip2location';

    const DESCRIPTION_EN = 'Separate keys for ip2location';

    public static function up()
    {
        $tokens = [
            'ip2location_full_isp_token',
            'ip2location_full_token',
            'ip2location_px2_token'
        ];
        $prefix = self::getPrefix();
        $sql = "SELECT `value` FROM `{$prefix}settings` WHERE `key` = 'ip2location_token'";
        $row = self::getDb()->getRow($sql);
        if ($row && is_array($row)) {
            $oldToken = $row['value'];
        } else {
            $oldToken = "";
        }
        if ($oldToken != "") {
            foreach($tokens as $t) {
                $change = "";
                $sql = "SELECT `value` FROM `{$prefix}settings` WHERE `key` = '{$t}'";
                $row = self::getDb()->getRow($sql);
                if (empty($row)) {
                    $change = "INSERT INTO `{$prefix}settings` (`key`, `value`) VALUES ('{$t}', '{$oldToken}')";
                } elseif (is_array($row) && $row['value'] == '') {
                    $change = "UPDATE `{$prefix}settings` SET `value` = '{$oldToken}' WHERE `key` = '{$t}'";
                }
                if ($change != "") {
                    self::execute($change);
                }
            }
        }
    }
}