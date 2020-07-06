<?php
class Migration_20161004102502_ExtractDefaultActions extends Migration 
{
    const DESCRIPTION_RU = 'Создание потоков из Default Action';

    const DESCRIPTION_EN = 'Create streams from default actions';

    public static function up()
    {
        foreach (self::allCampaigns() as $row) {
            if (!self::containsDefaultStream($row['id'])) {
                self::createDefaultStream($row['id'], $row['action_type'], $row['action_payload']);
            }
        }
    }

    private static function allCampaigns()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}campaigns";
        return self::getDb()->execute($sql);
    }

    private static function containsDefaultStream($campaignId)
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT COUNT(*) FROM {$prefix}streams WHERE type = 'default' and campaign_id = {$campaignId}";
        return self::getDb()->getOne($sql) > 0;
    }

    private static function createDefaultStream($campaignId, $actionType, $actionPayload)
    {
        if (empty($actionType)) {
            $actionType = 'http';
        }
        $prefix = self::getDb()->getPrefix();
        $sql = "INSERT INTO {$prefix}streams (`type`, `campaign_id`, `action_type`, `action_payload`) 
        VALUES ('default', '{$campaignId}', '{$actionType}', ". static::quote($actionPayload) .")";
        self::execute($sql);
    }

    public static function quote($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_null($value)) {
            return 'NULL';
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        } elseif (is_array($value)) {
            return array_map(function($n) {return static::quote($n);}, $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\032") . "'";
    }
}