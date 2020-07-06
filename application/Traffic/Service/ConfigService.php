<?php
namespace Traffic\Service;

use Core\Application\Application;

class ConfigService extends AbstractService
{
    const SANDBOX_ENGINE = 'sandbox_engine';
    const SANDBOX_FPM_PATH = 'sandbox_fpm_path';
    const SANDBOX_FCGI_PATH = 'sandbox_fcgi_path';

    private $_cnf;

    /**
     * @param $path
     */
    public function init($path)
    {
        $text = file_get_contents($path);
        $this->_cnf = parse_ini_string($text, true);
    }

    public function findConfigPath()
    {
        switch (true) {
            case Application::instance()->isTesting():
                $cnfFile = ROOT . '/application/config/config.testing.ini.php';
                break;
            default:
                $cnfFile = ROOT . '/application/config/config.ini.php';
                $legacyCnfFile = ROOT . '/application/config/config.ini';
                if (!file_exists($cnfFile) && file_exists($legacyCnfFile)) {
                    $cnfFile = $legacyCnfFile;
                }
        }
        return $cnfFile;
    }

    public function set($scope, $key, $value)
    {
        $this->_cnf[$scope][$key] = $value;
    }

    public function get($scope, $key = null, $default = null)
    {
        if ($this->has($scope, $key)) {
            if (isset($key)) {
                return $this->_cnf[$scope][$key];
            } else {
                return $this->_cnf[$scope];
            }
        } else {
            if (isset($default)) {
                return $default;
            }
            return null;
        }
    }

    public function delete($scope, $key)
    {
        unset($this->_cnf[$scope][$key]);
    }

    public function has($scope, $key = null)
    {
        return (isset($this->_cnf[$scope]) && (!isset($key) || isset($this->_cnf[$scope][$key])));
    }

    public function getDebugValue()
    {
        return (bool) ConfigService::instance()->get('system', 'debug');
    }

    public function isDemo()
    {
        return (bool) ConfigService::instance()->get('system', 'demo');
    }

    public function isReferrerRedefineAllowed()
    {
        return (bool) ConfigService::instance()->get('system', 'allow_change_referrer', false);
    }

}