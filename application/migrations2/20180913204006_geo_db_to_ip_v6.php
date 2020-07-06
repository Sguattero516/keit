<?php
class Migration_20180913204006_GeoDbToIpV6 extends Migration 
{
    const DESCRIPTION_RU = 'Перенос баз ip2location в ipv6 формат';

    const DESCRIPTION_EN = 'Move ip2location databases to ipv6 format';

    public static function up()
    {
        $path = dirname(__FILE__) . '/../../var/geoip/IP2Location/';
        $oldFile1 = $path . 'full/IP-COUNTRY-REGION-CITY.BIN';
        $newFile1 = $path . 'full/IPV6-COUNTRY-REGION-CITY.BIN';
        $oldFile2 = $path . 'full_isp/IP-COUNTRY-REGION-CITY-ISP.BIN';
        $newFile2 = $path . 'full_isp/IPV6-COUNTRY-REGION-CITY-ISP.BIN';
        if (is_file($oldFile1) && !is_file($newFile1)) {
            copy($oldFile1, $newFile1);
        }
        if (is_file($oldFile2) && !is_file($newFile2)) {
            copy($oldFile2, $newFile2);
        }
    }
}