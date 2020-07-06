<?php
class Migration_20161020230734_UserCampaignPermissions extends Migration 
{
    const DESCRIPTION_RU = 'Перенос авторизационных данных в user_campaign_permissions';

    const DESCRIPTION_EN = 'Move auth data to user_campaign_permissions';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'user_campaign_permissions` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` INT(11) NOT NULL,
          `campaign_id` INT(11) NOT NULL,
          `features` TEXT NULL,
          `metrics` TEXT NULL,
          PRIMARY KEY (`id`),
          INDEX `user_id_campaign_id_idx` (`user_id`, `campaign_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
        self::execute($sql);
        foreach(self::_getUserRules() as $user) {
            $userId = $user['id'];
            $rules = json_decode($user['rules'], true);
            foreach($rules as $rule) {
                $campaignId = $rule['id'];
                if (empty($campaignId) || !is_numeric($campaignId)) {
                    continue;
                }
                $features = NULL;
                if (isset($rule['permissions']) && self::checkValueValid($rule['permissions'])) {
                    $features = json_encode($rule['permissions']);
                }
                $features = \Core\Db\Db::quote($features);
                
                $metrics = NULL;
                if (isset($rule['scopes']) && self::checkValueValid($rule['scopes'])) {
                    $metrics = json_encode($rule['scopes']);
                }
                $metrics = \Core\Db\Db::quote($metrics);
                
                $sql = "INSERT IGNORE INTO {$prefix}user_campaign_permissions (`user_id`, `campaign_id`, `features`, `metrics`) VALUES
                    ('{$userId}', '{$campaignId}', {$features}, {$metrics})";
                self::execute($sql);
            }
        }
    }

    public static function checkValueValid($value)
    {
        if (!is_array($value)) {
            return false;
        }
        foreach($value as $element) {
            if (!is_string($element)) {
                return false;
            }
        }
        return true;
    }
    
    protected static function _getUserRules()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}users WHERE rules is not null and rules != '[]'";
        return self::getDb()->execute($sql);
    }
}