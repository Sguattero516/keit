<?php
class Migration_20171213112657_AddSubId15Clicks extends Migration
{
    const DESCRIPTION_RU = 'Добавление sub_id_11 - sub_id_15 в clicks';

    const DESCRIPTION_EN = 'Add sub_id_11 - sub_id_15 to clicks';

    public static function up($rerun = false)
    {
        if ($rerun) {
            self::addColumnsIfNotExist(
                'clicks',
                [
                    'sub_id_11_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_12_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_13_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_14_id' => 'int(10) unsigned DEFAULT NULL',
                    'sub_id_15_id' => 'int(10) unsigned DEFAULT NULL',
                ],
                true
            );
        }
    }
}