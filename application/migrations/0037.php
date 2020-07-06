<?php
use Core\Model\Shard;
use Traffic\Service\SettingsService;
use Services\ShardService;


class Migration_37 extends Migration {

    const DESCRIPTION_RU = 'Добавление extra_param_[1-7] и sub_id_[1,3-4] в keitaro_stats_*';

    const DESCRIPTION_EN = 'Add additional extra_param_[1-7] and new sub_id_[1,3-4] keitaro_stats_*';

    public static function up()
    {
        // removed
    }
}