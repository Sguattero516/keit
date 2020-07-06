<?php
class Migration_98 extends Migration {

    const DESCRIPTION_RU = 'Увеличение размера sub_id_n';

    const DESCRIPTION_EN = 'Increase size of sub_id_n';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "
        ALTER TABLE `{$prefix}conversions` CHANGE `sub_id_1` `sub_id_1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `sub_id_2` `sub_id_2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `sub_id_3` `sub_id_3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `sub_id_4` `sub_id_4` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_1` `extra_param_1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_2` `extra_param_2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_3` `extra_param_3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_4` `extra_param_4` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_5` `extra_param_5` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_6` `extra_param_6` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
         CHANGE `extra_param_7` `extra_param_7` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
         ";
        self::execute($sql);
    }
}