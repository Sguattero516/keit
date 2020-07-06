<?php
class Migration_20180330131635_AddAffiliateNetworkPullApi extends Migration 
{
    const DESCRIPTION_RU = 'Добавление pull_api_options в affiliate_networks';

    const DESCRIPTION_EN = 'Add pull_api_options to affiliate_networks';

    public static function up()
    {
        $addIndex = false;
        self::addColumnsIfNotExist(
            'affiliate_networks', 
            [
                'pull_api_options' => 'TEXT',
            ],
            $addIndex
        );
    }
}