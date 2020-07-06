<?php
class Migration_20161007163321_MigrateBotActionsToForcedActions extends Migration 
{
    const DESCRIPTION_RU = 'Перенос действий для ботов в приоритетные потоки';

    const DESCRIPTION_EN = 'Move bot actions to forced streams';

    public static function up()
    {
        foreach (static::getCampaigns() as $campaign) {
            if (static::noForcedStreams($campaign['id'])) {
                static::createForcedStream($campaign['id'],
                    $campaign['action_for_bots'],
                    $campaign['bot_redirect_url'],
                    $campaign['bot_text']
                );
            }
        }
    }

    public static function getCampaigns()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}campaigns WHERE action_for_bots <> 'do_nothing'";
        return self::getDb()->execute($sql);
    }

    public static function noForcedStreams($campaignId)
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT COUNT(*) FROM {$prefix}streams WHERE `type` = 'forced' AND campaign_id = '{$campaignId}'";
        return self::getDb()->getOne($sql) == 0;
    }

    public static function createForcedStream($campaignId, $botAction, $url, $text)
    {
        $actionType = null;
        $actionPayload = null;
        switch ($botAction) {
            case '404':
                $actionType = 'status404';
                break;
            case 'redirect':
                $actionType = 'http';
                $actionPayload = $url;
                break;
            case 'show_text':
                $actionType = 'show_text';
                $actionPayload = $text;
                break;

        }
        if ($actionType) {
            $actionPayload = \Core\Db\Db::quote($actionPayload);
            $prefix = self::getDb()->getPrefix();
            $sql = "INSERT INTO {$prefix}streams (campaign_id, `type`, action_type, action_payload, collect_clicks) VALUES
                ('{$campaignId}', 'forced', '{$actionType}', {$actionPayload}, 0)";
            self::execute($sql);

            $id = self::getDb()->getInsertId();

            static::createFilter($id);
        }
    }

    public static function createFilter($id)
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "INSERT INTO {$prefix}stream_filters (stream_id, name, mode) VALUES
            ('{$id}', 'bot', 'accept')";
        self::getDb()->execute($sql);
    }
}