<?php
use Core\Db\Db;

class Migration_20170506193451_ConvertAclTypes extends Migration 
{
    const DESCRIPTION_RU = 'Конвертация acl в новый формат';

    const DESCRIPTION_EN = 'Convert acl to new format';

    const CREATED_BY_USER = 'created_by_user';
    const CREATED_BY_USER_AND_GROUPS = 'created_by_user_and_groups';
    const TO_GROUPS = 'to_groups';
    const CREATED_BY_USER_AND_SELECTED = 'created_by_user_and_selected';
    const TO_SELECTED = 'to_selected';
    const CREATED_BY_USER_GROUPS_AND_SELECTED = 'created_by_user_groups_and_selected';
    const TO_GROUPS_AND_SELECTED = 'to_groups_and_selected';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        Db::instance()->beginTransaction();
        $sql = "SELECT * FROM {$prefix}acl";
        foreach (self::getDb()->execute($sql) as $row) {
            if (in_array($row['access_type'], [
                    self::CREATED_BY_USER_AND_GROUPS, 
                    self::CREATED_BY_USER_AND_SELECTED, 
                    self::CREATED_BY_USER
            ])) {
                $sql = "UPDATE {$prefix}acl SET access_type = " . 
                    Db::quote(self::CREATED_BY_USER_GROUPS_AND_SELECTED) . 
                    "WHERE id = " . Db::quote($row['id']);
                self::getDb()->execute($sql);
            } elseif (in_array($row['access_type'], [
                    self::TO_GROUPS, 
                    self::TO_SELECTED
                ])) {
                $sql = "UPDATE {$prefix}acl SET access_type = " . 
                    Db::quote(self::TO_GROUPS_AND_SELECTED) . 
                    "WHERE id = " . Db::quote($row['id']);
                self::getDb()->execute($sql);
            }
        }
        Db::instance()->commit();
    }
}