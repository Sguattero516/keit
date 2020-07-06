<?php
use Core\Json\Json;

class Migration_69 extends Migration {

    const DESCRIPTION_RU = 'Конвертация прав доступа в новый формат у пользователей';

    const DESCRIPTION_EN = 'Convert users permissions to the new format';

    public static function up()
    {
        $replaceScopes = array(
            'hourly' => 'hours',
            'daily' => 'campaigns',
            'referers' => 'referrers',
            'visitors' => 'detailed',
            'search_engines' => 'se',
            'sales' => 'conversions'
        );

        $replaceGlobalPermissions = array(
            'create_group' => 'create_campaign'
        );

        $sql = 'SELECT * FROM ' . self::getDb()->getPrefix() . 'users';
        foreach (self::getDb()->execute($sql) as $row) {
            $rules = self::decode($row['rules']);
            $rules = self::_fixRules($rules);

            foreach ($rules as $id => $data) {
                if (isset($data['scopes']) && is_array($data['scopes'])) {
                    $newScopes = array();
                    foreach ($data['scopes'] as $scope) {
                        if (isset($replaceScopes[$scope])) {
                            $newScopes[] = $replaceScopes[$scope];
                        } else {
                            $newScopes[] = $scope;
                        }
                    }
                    $rules[$id]['scopes'] = $newScopes;
                }
            }
            $permissions = self::decode($row['permissions']);
            $newPermissions = array();
            if (is_array($permissions)) {
                foreach ($permissions as $permission) {
                    if (isset($replaceGlobalPermissions[$permission])) {
                        $newPermissions[] = $replaceGlobalPermissions[$permission];
                    } else {
                        $newPermissions[] = $permission;
                    }
                }
            }

            $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'users '.
                ' SET permissions = ' . self::getDb()->quote(json_encode($newPermissions)) . ', '.
                ' rules = ' . self::getDb()->quote(json_encode($rules)) .
                ' WHERE id = ' . self::getDb()->quote($row['id']);

            \Traffic\Logging\Service\LoggerService::instance()->error($sql);

            self::execute($sql);
        }
    }

    private static function decode($data)
    {
        if ($data[0] == 'a') {
            $data = unserialize($data);
        } else {
            $data = json_decode($data, true);
        }
        return $data;
    }

    protected static function _fixRules($current)
    {
        $rules = array();
        if (!is_array($current)) {
            return $rules;
        }
        foreach ($current as $id => $data) {
            if (!is_array($data)) {
                $data = array('permission' => $data);
            }

            if (!isset($data['permissions'])) {
                $data['permissions'] = array();
                if (isset($data['permission'])) {
                    $data['permissions'] = $data['permission'] == 'edit'
                        ? array('edit') : array();
                    unset($data['permission']);
                }
            }

            if (!isset($data['id'])) {
                $data['id'] = $id;
            }
            $rules[] = $data;
        }

        return $rules;
    }

}