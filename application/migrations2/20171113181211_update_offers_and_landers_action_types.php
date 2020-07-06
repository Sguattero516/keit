<?php
class Migration_20171113181211_UpdateOffersAndLandersActionTypes extends Migration
{
    const DESCRIPTION_RU = 'Обновление action_type в offers и landings';

    const DESCRIPTION_EN = 'Update action_type in offers and landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $items = [
            'preloaded' => 'curl',
            'external' => 'http',
            'local' => 'local_file'
        ];

        foreach ($items as $itemType => $actionType) {
            $sql = "UPDATE {$prefix}landings SET action_type = '{$actionType}' WHERE landing_type = '{$itemType}'";
            self::execute($sql);
        }

        foreach ($items as $itemType => $actionType) {
            $sql = "UPDATE {$prefix}offers SET action_type = '{$actionType}' WHERE offer_type = '{$itemType}'";
            self::execute($sql);
        }
    }
}