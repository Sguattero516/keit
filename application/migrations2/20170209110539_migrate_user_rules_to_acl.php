<?php
use Component\Users\Model\AclRule;
use Core\Db\Db;

class Migration_20170209110539_MigrateUserRulesToAcl extends Migration 
{
    const DESCRIPTION_RU = 'Миграция pub_rules в acl';

    const DESCRIPTION_EN = 'Migration pub_rules to acl';

    public static function up()
    {
        $prefix = self::getPrefix();

        $permissions = Db::instance()->execute("SELECT * FROM {$prefix}user_campaign_permissions");
        $aclData = [];
        Db::instance()->beginTransaction();
        foreach ($permissions as $permission) {
            $userId = $permission['user_id'];
            $campaignId = $permission['campaign_id'];
            $features = $permission['features'];
            $aclData[$userId]['campaign_id'][] = $campaignId;
            if ($aclData[$userId]['access_type'] != 'read_only') {
                if (in_array('edit', $features)) {
                    $aclData[$userId]['access_type'] = 'created_by_user_and_selected';
                } else {
                    $aclData[$userId]['access_type'] = 'read_only';
                }
            }
        }
        foreach ($aclData as $userId => $data) {
            $tableName = AclRule::getTableName();
            $sql = "INSERT INTO {$tableName}(`user_id`, `access_type`, `entity_type`, `entities`) VALUES(" . 
                Db::quote($userId) . ',' . 
                Db::quote($data['access_type']) . ',' . 
                Db::quote('campaign') . ',' .
                Db::quote(json_encode($data['campaign_id'], true)) . 
            ')';
            self::execute($sql);
        }
        Db::instance()->commit();
    }
}