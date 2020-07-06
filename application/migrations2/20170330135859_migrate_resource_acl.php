<?php
use Core\Db\Db;

class Migration_20170330135859_MigrateResourceAcl extends Migration 
{
    const DESCRIPTION_RU = 'Миграция users.permissions в acl';

    const DESCRIPTION_EN = 'Migration users.permissions to acl';

    private static function allUsers()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}users";
        return self::getDb()->execute($sql);
    }

    private static function resourcesByUser($userId)
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}acl_resources where user_id = " . Db::quote($userId);
        return self::getDb()->getRow($sql);
    }
    
    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        Db::instance()->beginTransaction();
        foreach (self::allUsers() as $row) {
            if ($row['type'] == 'ADMIN') {
                continue;
            }
            $permissions = $row['permissions'];
            if (!is_string($permissions)) {
                continue;
            }
            $permissions = json_decode($permissions, true);
            if (!is_array($permissions)) {
                continue;
            }
            if (empty($permissions)) {
                continue;
            }
            $resources = [];
            if (in_array('create_campaign', $permissions)) {
                $resources = [
                    'offers',
                    'landings',
                    'campaigns',
                    'affiliate_networks',
                    'traffic_sources',
                    'streams',
                    'reports',
                    'trends',
                    'groups',
                    'clicks',
                ];
            }
            if (in_array('view_dashboard', $permissions)) {
                $resources[] = 'dashboard';
            }
            if (!empty($resources)) {
                $userResources = self::resourcesByUser($row['id']);
                $oldResources = [];
                if ($userResources) {
                    if (is_string($userResources['resources'])) {
                        $oldResources = json_decode($userResources['resources'], true);
                        if (!is_array($oldResources)) {
                            $oldResources = [];
                        }
                    }
                }
                $resources = array_merge($resources, $oldResources);
                $resources = array_unique($resources);
                if (!$userResources) {
                    $sql = "INSERT INTO {$prefix}acl_resources(`user_id`, `resources`) values (" . 
                        Db::quote($row['id']) . ", " .
                        Db::quote(json_encode($resources)) . ")";
                    self::getDb()->execute($sql);
                } else {
                    $sql = "UPDATE {$prefix}acl_resources SET resources = " . 
                        Db::quote(json_encode($resources)) . " WHERE user_id = " .
                        Db::quote($row['id']);
                    self::getDb()->execute($sql);
                }
            }
        }
        Db::instance()->commit();
    }
}