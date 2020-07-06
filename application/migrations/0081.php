<?php
class Migration_81 extends Migration {

    const DESCRIPTION_RU = 'Добавление прав на просмотр отчетов текущим пользователям';

    const DESCRIPTION_EN = 'Add permission to see reports to current users';

    public static function up()
    {
        $prefix =  self::getDb()->getPrefix();
        $sql = "SELECT * FROM {$prefix}users WHERE `type` = 'USER'";
        foreach (self::getDb()->execute($sql) as $row) {
            $toSave = false;
            $rules = json_decode($row['rules'], true);
            foreach ($rules as &$rule) {
                if (!empty($rule['permissions']) && is_array($rule['permissions']) && !in_array('reports', $rule['permissions'])) {
                    $rule['permissions'][] = 'reports';
                    $toSave = true;
                }
            }

            if ($toSave) {
                $rules = \Core\Json\json_encode($rules);
                $sql = "UPDATE {$prefix}users SET rules = " . self::getDb()->quote($rules) . " WHERE id = " . $row['id'];
                self::execute($sql);
            }
        }
    }
}