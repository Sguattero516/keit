<?php
class Migration_20171213112709_AddSubId15Conversions extends Migration
{
    const DESCRIPTION_RU = 'Добавление sub_id_11 - sub_id_15 в conversions';

    const DESCRIPTION_EN = 'Add sub_id_11 - sub_id_15 to conversions';

    public static function up($rerun = false)
    {
        if ($rerun) {
            self::addColumnsIfNotExist(
                'conversions_2',
                [
                    'sub_id_11_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_12_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_13_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_14_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_15_id' => 'int(10) unsigned DEFAULT NULL',
                ],
                false
            );
        }
    }
}