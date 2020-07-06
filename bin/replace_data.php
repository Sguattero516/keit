<?php
use Core\EntityEventManager\Service\EntityEventService;

if (php_sapi_name() !== 'cli') {
    die('403 Forbidden');
}


define('ROOT', realpath(dirname(__FILE__) . '/../'));

require(ROOT . '/bin/_init.php');

$data = file_get_contents(ROOT . '/db_data.sql');

if (empty($data)) {
    throw new \Exception('Empty file db_data.sql');
}

$sqls = explode("\n", $data);
foreach ($sqls as $sql) {
    if (trim($sql)) {
        echo $sql."\n";
        try {
            \Core\Db\Db::instance()->execute($sql);
        } catch (ADODB_Exception $e) {
            echo $e->getMessage()."\n";
        }
    }
}

\Traffic\CachedData\Repository\CachedDataRepository::instance()->warmup();

echo 'done!';