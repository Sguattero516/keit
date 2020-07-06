<?php
class Migration_20170813111110_DenormalizeAffiliateNetwork extends Migration 
{
    const DESCRIPTION_RU = 'Добавление affiliate_network_id к clicks';

    const DESCRIPTION_EN = 'Add affiliate_network_id to clicks';

    public static function up($rerun = false)
    {
        $prefix = self::getPrefix();
        try {
            $sql = "ALTER IGNORE TABLE {$prefix}clicks ADD `affiliate_network_id` INT UNSIGNED NULL DEFAULT NULL AFTER offer_id";
            self::execute($sql);
        } catch (ADODB_Exception $e) {
            $sql = "ALTER TABLE {$prefix}clicks ADD `affiliate_network_id` INT UNSIGNED NULL DEFAULT NULL AFTER offer_id";
            self::silentExecute($sql);
        }

        $sql = "ALTER TABLE {$prefix}clicks ADD INDEX affiliate_network_id(affiliate_network_id)";
        self::silentExecute($sql);

        $sql = "ALTER TABLE {$prefix}conversions_2 ADD `affiliate_network_id` INT UNSIGNED NULL DEFAULT NULL AFTER offer_id";
        self::silentExecute($sql);

        $sql = "ALTER TABLE {$prefix}conversions_2 ADD INDEX affiliate_network_id(affiliate_network_id)";
        self::silentExecute($sql);

        $count = self::getDb()->getOne("SELECT count(*) from {$prefix}clicks");
        if ($count < 1000000 || $rerun) {
            $sql = "UPDATE {$prefix}clicks c
                    LEFT JOIN {$prefix}offers o ON
                        c.offer_id = o.id AND 
                        o.affiliate_network_id is not null
                    SET c.affiliate_network_id = o.affiliate_network_id";
            self::execute($sql);
            $sql = "UPDATE {$prefix}conversions_2 c
                    LEFT JOIN {$prefix}offers o ON
                        c.offer_id = o.id AND 
                        o.affiliate_network_id is not null
                    SET c.affiliate_network_id = o.affiliate_network_id";
            self::execute($sql);
        }
    }
}