<?php
use Traffic\Service\SettingsService;


class Migration_17 extends Migration {

    const DESCRIPTION_RU = 'Добавление настройки store_sales_period';

    const DESCRIPTION_EN = 'Add setting store_sales_period';

    public static function up()
    {
        SettingsService::instance()->updateValue('store_sales_period', 30);
    }
}