<?php
namespace {
define('RESOLVE_METHOD', 'domain');
}

namespace Models {
interface StepModelInterface
{
    public function getCurrentStep();
    public function getCurrentStepData();
    public function updateStep($newStep);
}
}

namespace Models {
use mysql_xdevapi\Exception;
use Services\StatusCheckerService;
use Services\ConfigService;
use Services\IdentityService;
use Services\DownloadService;
use Services\ParameterService;
use Services\ArchiveService;
use Services\FileService;
use Services\KLocaleService;
use Services\MysqlService;
use Services\RedirectService;
use ResultSets\Step1ResultSet;
use ResultSets\Step3ResultSet;
use ResultSets\SingleElementResultSet;
use ResultSets\GeneralResult;
use ResultSets\ElementArrayResultSet;
class StepModel implements StepModelInterface
{
    const RESULT_PREFIX = 'success::';
    private $_step = 1;
    private $_isCli = false;
    private $_versions = array('6' => '/releases/v6/', '7' => '/releases/v7/', '8' => '/releases/v8/', '9' => '/releases/v9/');
    private $_releasesPath = '/releases';
    const PACKAGE_PHP7 = 'package7';
    const PACKAGE_PHP71 = 'package8';
    public function __construct($isCli = false)
    {
        $this->_isCli = $isCli;
        if (isset($_GET['step'])) {
            $this->_step = $_GET['step'];
        }
        if (isset($_POST['step'])) {
            $this->_step = $_POST['step'];
        }
    }
    public function getCurrentStep()
    {
        return $this->_step;
    }
    public function getCurrentStepData()
    {
        $action = 'step' . $this->_step;
        if (method_exists($this, $action)) {
            return $this->{$action}();
        } else {
            return array();
        }
    }
    public function updateStep($newStep)
    {
        $this->_step = $newStep;
    }
    public function step1()
    {
        $ip = false;
        if ($this->_isCli) {
            $ip = ParameterService::instance()->getPersistent(ParameterService::IP);
        }
        if (!$ip) {
            $ip = IdentityService::instance()->getIp();
        }
        $data = new Step1ResultSet();
        $data->ip = StatusCheckerService::checkIpStatus($ip);
        $data->ioncube = StatusCheckerService::checkIoncubeStatus();
        $data->php = StatusCheckerService::checkPhpVersion();
        $data->mysql = StatusCheckerService::checkMysqlStatus();
        $data->safemode = StatusCheckerService::checkSafemodeStatus();
        $data->xcache = StatusCheckerService::checkXcacheStatus();
        $data->iconv = StatusCheckerService::checkIconvStatus();
        $data->mbstring = StatusCheckerService::checkMbStatus();
        $data->curl = StatusCheckerService::checkCurlStatus();
        $data->zlib = StatusCheckerService::checkZlibStatus();
        $data->zip = StatusCheckerService::checkZipStatus();
        $data->json = StatusCheckerService::checkJsonStatus();
        $data->hash = StatusCheckerService::checkHashStatus();
        $data->permissions = StatusCheckerService::checkDirStatus();
        return $data;
    }
    public function step2()
    {
        $data = new SingleElementResultSet();
        $license = ParameterService::instance()->getLicense();
        if ($license === false) {
            $data->element = new GeneralResult(false, "");
            return $data;
        }
        $result = IdentityService::instance()->validateLicense($license);
        if ($result->getStatus()) {
            $data->element = IdentityService::instance()->saveLicense($license);
        } else {
            $data->element = $result;
        }
        return $data;
    }
    public function step3()
    {
        $data = new Step3ResultSet();
        if (is_dir('./www')) {
            $data->www = new GeneralResult(false, "");
        } else {
            $data->www = new GeneralResult(true, "");
        }
        if (is_file('./index.php')) {
            $data->index = new GeneralResult(false, "");
        } else {
            $data->index = new GeneralResult(true, "");
        }
        if (ConfigService::instance()->isKeitaro7()) {
            $data->has7Problem = new GeneralResult(true, "");
        } else {
            $data->has7Problem = new GeneralResult(false, "");
        }
        return $data;
    }
    public function step4($localPackage = 'package.pack')
    {
        $data = new SingleElementResultSet();
        $package = ParameterService::instance()->getCustomPackage();
        $phpVersion = phpversion();
        if (!$package) {
            $majorVersionNumber = ParameterService::instance()->getMajorVersionNumber();
            if ($majorVersionNumber === false) {
                $data->element = new GeneralResult(false, "");
                return $data;
            }
            $package = $this->_getPackageUrl($majorVersionNumber, $phpVersion);
        }
        $data->element = DownloadService::instance()->download($package, $localPackage);
        return $data;
    }
    public function step4_2()
    {
        $data = new SingleElementResultSet();
        $localPackage = 'package.pack';
        if (!file_exists($localPackage) || !filesize($localPackage)) {
            $data->element = new GeneralResult(false, KLocaleService::t('step4.download_error'));
            return $data;
        }
        $result = ArchiveService::instance()->unpack($localPackage);
        if (!$result->getStatus()) {
            $data->element = $result;
            return $data;
        }
        if (file_exists('www/version.php')) {
            FileService::instance()->dirmv('www', './', true);
        }
        if (!file_exists('version.php')) {
            $data->element = new GeneralResult(false, KLocaleService::t('step4.unpack_error'));
            return $data;
        }
        if (ConfigService::instance()->isKeitaro9()) {
            FileService::instance()->rmdir('application/Component/Backup');
        }
        $uid = fileowner(dirname(__FILE__));
        $gid = filegroup(dirname(__FILE__));
        FileService::instance()->chmod('var', 0777);
        FileService::instance()->chown('./', $uid, $gid);
        FileService::instance()->chown('www', $uid, $gid);
        @mkdir('var/cache', 0777);
        @chmod('var/cache', 0777);
        @mkdir('var/cron', 0777);
        @chmod('var/cron', 0777);
        @mkdir('var/exports', 0777);
        @mkdir('var/log', 0777);
        @mkdir('var/sessions', 0777);
        @chmod(ConfigService::instance()->getConfigIniPath(), 0777);
        @unlink($localPackage);
        FileService::instance()->rmdir('www');
        $data->element = new GeneralResult(true, "");
        return $data;
    }
    public function step6()
    {
        $data = new ElementArrayResultSet();
        foreach (ConfigService::instance()->getRemoteFiles() as $archiveName => $archiveData) {
            $url = $archiveData['dataUrl'];
            $crcUrl = $archiveData['crcUrl'];
            $file = $archiveData['path'];
            $archive = $archiveData['path'] . ".gz";
            if (!file_exists($file)) {
                $result = DownloadService::instance()->downloadAndValidate($url, $crcUrl, $archive, $file);
                if (!$result->getStatus()) {
                    $data->addElement($result);
                }
            }
            if (!file_exists($file)) {
                $data->addElement(new GeneralResult(false, KLocaleService::t('step6.cant_download_file')));
            }
        }
        return $data;
    }
    public function step7()
    {
        $data = new SingleElementResultSet();
        $parameterService = ParameterService::instance();
        if (!$parameterService->getHostName() && !$this->_isCli) {
            $data->element = new GeneralResult(true, "");
            return $data;
        }
        $h = $parameterService->getHostName();
        $u = $parameterService->getUserName();
        $p = $parameterService->getPassword();
        $d = $parameterService->getDbName();
        $pr = $parameterService->getPrefixParam();
        $r = $parameterService->getRedisUri();
        $data->element = MysqlService::instance()->checkDatabase($h, $u, $p, $d);
        if ($data->getStatus()) {
            $data->element = ConfigService::instance()->updateConfigFile($h, $u, $p, $d, $pr, $r);
            if ($data->getStatus()) {
                if ($this->_isCli) {
                    $this->_step = 8;
                    return false;
                } else {
                    RedirectService::instance()->toStep(8);
                }
            }
        }
        return $data;
    }
    public function step8()
    {
        ConfigService::instance()->updateIniConfig();
        $result = MysqlService::instance()->connectFromIni();
        if ($result->getStatus()) {
            $result = MysqlService::instance()->executeSql();
            if (!ConfigService::instance()->isKeitaro7() && MysqlService::instance()->hasTokuDb()) {
                MysqlService::instance()->updateEngineToTokuDb();
            }
        }
        $data = new SingleElementResultSet();
        $data->element = $result;
        return $data;
    }
    public function step9()
    {
        $data = new ElementArrayResultSet();
        ConfigService::instance()->updateIniConfig();
        $login = ParameterService::instance()->getKeitaroLogin();
        $pass = ParameterService::instance()->getKeitaroPass();
        $passConfirm = ParameterService::instance()->getKeitaroPassConfirm();
        $language = ParameterService::instance()->getLanguage();
        $currency = ParameterService::instance()->getCurrency();
        $extra = ParameterService::instance()->getExtraUrl();
        $draft = ParameterService::instance()->getDraftStorage();
        $cache = ParameterService::instance()->getCacheStorage();
        $timezone = ParameterService::instance()->getTimezone();
        $stabilityChannel = ParameterService::instance()->getStabilityChannel();
        $result = MysqlService::instance()->connectFromIni();
        if (!$login) {
            return $data;
        }
        if (!trim($login)) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.login_error')));
        }
        if (!trim($pass)) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.password_error')));
        }
        if ($pass != $passConfirm) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.passwords_mismatch')));
        }
        if (!trim($extra)) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.extra_url_error')));
        } elseif (!stristr($extra, 'http://') && !stristr($extra, 'https://')) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.extra_url_incorrect')));
        }
        if ($draft == 'redis' && !StatusCheckerService::instance()->checkRedisStatus()->getStatus()) {
            $data->addElement(new GeneralResult(false, KLocaleService::t('step9.draft_no_redis')));
        }
        if (!$data->getStatus()) {
            return $data;
        }
        MysqlService::instance()->setSetting('extra_url', $extra);
        $result = ConfigService::instance()->updateIniStore();
        if (!$result->getStatus()) {
            $data->addElement($result);
            return $data;
        }
        MysqlService::instance()->setAdminPass($login, $pass);
        if ($language) {
            MysqlService::instance()->setLanguage($language);
        }
        if ($currency) {
            MysqlService::instance()->setCurrency($currency);
        }
        MysqlService::instance()->setDraftStorage($draft);
        MysqlService::instance()->setCacheStorage($cache);
        MysqlService::instance()->setStabilityChannel($stabilityChannel);
        MysqlService::instance()->setInstalledAt();
        ParameterService::instance()->savePersistent('login', $login);
        ParameterService::instance()->savePersistent('password', $pass);
        if (!ConfigService::instance()->isKeitaro7() && ConfigService::instance()->isValidTimezone($timezone)) {
            MysqlService::instance()->setTimezone($timezone);
        }
        if ($this->_isCli) {
            $this->_step = 10;
            return false;
        } else {
            RedirectService::instance()->toStep(10);
            return $data;
        }
    }
    public function step12()
    {
        touch('var/install.lock');
        ParameterService::instance()->removeAllPersistent();
    }
    private function _getPackageUrl($majorVersionNumber, $phpVersion)
    {
        $stability = ParameterService::instance()->getStabilityChannel();
        $url = ConfigService::SITENAME . "/license/api/checkUpdate?version={$majorVersionNumber}&stability={$stability}&phpversion={$phpVersion}";
        $result = DownloadService::instance()->request($url);
        if ($result->getStatus() != 200) {
            throw new \Exception('Can\'t receive stable version');
        }
        $majorVersionNumber = str_replace(self::RESULT_PREFIX, '', $result->getDesc());
        $filename = $this->_getPackageName($phpVersion);
        $ext = $this->_getCompatibleExtension();
        return ConfigService::SITENAME . $this->_releasesPath . '/' . $majorVersionNumber . '/' . $filename . '.' . $ext;
    }
    private function _getCompatibleExtension()
    {
        if (class_exists('ZipArchive')) {
            return 'zip';
        } else {
            return 'tar.gz';
        }
    }
    private function _getPackageName($phpVersion)
    {
        if (version_compare($phpVersion, '5.6') >= 0 && version_compare($phpVersion, '7.1') < 0) {
            return self::PACKAGE_PHP7;
        } elseif (version_compare($phpVersion, '7.1') >= 0) {
            return self::PACKAGE_PHP71;
        }
        throw new \Exception($phpVersion . ' is not compatible');
    }
}
}

namespace Views {
interface ViewInterface
{
    public function renderStepPage($step, $data);
    public function renderLockedPage();
}
}

namespace Views {
use ResultSets\Step1ResultSet;
use ResultSets\Step3ResultSet;
use Services\KLocaleService;
use Services\ConfigService;
use Services\ParameterService;
use Services\RedirectService;
use Services\StatusCheckerService;
use Services\IdentityService;
use ResultSets\SingleElementResultSet;
use ResultSets\ElementArrayResultSet;
class WebView implements ViewInterface
{
    const MAIN_TEMPLATE = <<<TPL
<!DOCTYPE html>
<html>
<head>
    <title>#TITLE# / #MAIN_TITLE#</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex">
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="#DOMAIN#/installer/v2/app.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="#DOMAIN#/installer/v2/app.css" type="text/css">
</head>
<body data-current-step="#STEP#">
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <nav class="steps">
                <div class="step current" data-step-to="1">1</div> <!-- Checker -->
                <div class="step"  data-step-to="2">2</div> <!-- Key -->
                <div class="step"  data-step-to="4">3</div> <!-- Files -->
                <div class="step"  data-step-to="5">4</div> <!-- DBs -->
                <div class="step"  data-step-to="7">5</div> <!-- DB -->
                <div class="step"  data-step-to="9">6</div> <!-- Admin -->
                <div class="step"  data-step-to="10">7</div> <!-- Cron -->
            </nav>
            
            <article>
                <form action="install.php" method="post" class="panel panel-default">
                    <div class="panel-heading">
                        #TITLE#
                    </div>
                    <div class="panel-body">
                        #CONTENT#
                    </div>
                    <div class="panel-footer">
                        #FOOTER#
                    </div>
                </form>    
            </article>
            <footer class="text-center">
                <p><a href="?step=#CURRENT_STEP#&lang=#ANOTHER_LANG#">#CHANGE_LANG#</a></p>
                <p><a target="_blank" href="http://#WIKI#/installation">#DOCUMENTATION#</a></p>
            </footer>
        </div>    
    </div>
</div>
TPL;
    const STEP3_JS = <<<JSCODE
<script>
\$( document ).ready(function() {
    function updateButton(checkbox) {
        var button = \$("#submitButton");
        button.attr("disabled", !checkbox.prop('checked'));
    }
    var checkbox = \$('#indexCheckbox');
    checkbox.click(function() {
        updateButton(checkbox);
    });
    
    updateButton(checkbox);
});
</script>
JSCODE;
    private $_title;
    private $_content;
    private $_footer;
    public function __construct()
    {
        header('Content-Type: text/html; charset=utf-8');
    }
    private function _step1(Step1ResultSet $data)
    {
        $tableData = array();
        $this->_title = KLocaleService::t('step1.title');
        $this->_content .= '<table class="table compatibility-table">' . "\r\n";
        foreach ($data as $key => $value) {
            $row = $data->getMemberRow($key);
            $header = $row[Step1ResultSet::HEADER_COL];
            $text = $row[Step1ResultSet::STATUS_COL] ? $this->_wrapSuccess($row[Step1ResultSet::TEXT_COL]) : $this->_wrapError($row[Step1ResultSet::TEXT_COL]);
            $this->_content .= '<tr><th>' . $header . ':</th><td>' . $text . '</td>';
        }
        $this->_content .= '</table>';
        if ($data->getStatus()) {
            if (defined('CHECKER') && CHECKER) {
                $this->_footer .= KLocaleService::t('step1.success') . "\r\n";
            } else {
                $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="2" type="submit">' . KLocaleService::t('continue') . '</button>' . "\r\n";
            }
        } else {
            $this->_footer .= '<p class="status-error">' . KLocaleService::t('step1.failure') . '.</p>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="1" type="submit">' . KLocaleService::t('step1.retry') . '</button>' . "\r\n";
        }
    }
    private function _step2(SingleElementResultSet $data)
    {
        $this->_title = KLocaleService::t('step2.title');
        $this->_content .= '<label>' . KLocaleService::t('step2.enter_key') . '</label>';
        $this->_content .= '<input autofocus type="text" class="form-control" name="licenseKey" placeholder="XXXX-XXXX-XXXX-XXXX"/>';
        $this->_content .= '<div class="help-block">' . KLocaleService::t('step2.ip') . ' <b>' . IdentityService::instance()->getIp() . '</b></div>';
        if ($data->getStatus()) {
            RedirectService::instance()->toStep(3);
            $this->_footer .= '<button class="btn btn-success" name="step" value="3" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            $this->_footer .= '<div class="status-error">' . $data->getErrorText() . '</div>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="2" type="submit">' . KLocaleService::t('step2.check') . '</button>';
        }
    }
    private function _step3(Step3ResultSet $data)
    {
        $this->_title = KLocaleService::t('step3.title');
        if (!$data->getStatus()) {
            $this->_footer .= '<div class="status-error">' . KLocaleService::t('step3.delete_www') . '</div>';
        } else {
            $this->_content = '<b>' . KLocaleService::t('step3.choose_version') . '</b>';
            $this->_content .= '<div class="radio"><label><input checked type="radio" name="version" value="9" />Keitaro Tracker</label></div>';
            $this->_content .= '<div>' . KLocaleService::t('step3.description') . '</div>';
            if ($data->has7Problem->getStatus()) {
                $this->_content .= '<div class="alert alert-danger">' . KLocaleService::t('step3.has7warning') . '</div>';
            }
            $this->_content .= '<div class="alert alert-warning">' . KLocaleService::t('step3.warning') . '</div>';
            if (!$data->index->getStatus()) {
                $this->_content .= '<div><label><input type="checkbox" name="index" id="indexCheckbox" value="0">&nbsp;' . KLocaleService::t('step3.ignore_index') . '</label></div>';
                $this->_content .= self::STEP3_JS;
            }
            $this->_footer .= '<button autofocus id="submitButton" class="btn btn-success" name="step" value="4" type="submit">' . KLocaleService::t('step3.start') . '</button>';
        }
    }
    private function _step4(SingleElementResultSet $data)
    {
        $this->_title = KLocaleService::t('step4.title');
        if ($data->getStatus()) {
            $this->_content .= KLocaleService::t('step4.success');
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="4_2" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            $this->_content .= '<div class="status-error">' . $data->getErrorText() . '</div>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="3" type="submit">' . KLocaleService::t('retry') . "</button>";
        }
    }
    private function _step4_2(SingleElementResultSet $data)
    {
        $this->_title = KLocaleService::t('step4.title');
        if ($data->getStatus()) {
            $this->_content .= KLocaleService::t('step4.unpacked');
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="5" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            $this->_content .= '<div class="status-error">' . $data->getErrorText() . '</div>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="3" type="submit">' . KLocaleService::t('retry') . "</button>";
        }
    }
    private function _step5($data)
    {
        $this->_title = KLocaleService::t('step5.title');
        $this->_content .= KLocaleService::t('step5.description');
        $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="6" type="submit">' . KLocaleService::t('continue') . '</button>';
    }
    private function _step6(ElementArrayResultSet $data)
    {
        $this->_title = KLocaleService::t('step6.title');
        if ($data->getStatus()) {
            $this->_content .= KLocaleService::t('step6.success');
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="7" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            foreach ($data->getErrorTexts() as $text) {
                $this->_footer .= '<div class="status-error">' . $text . '</div>';
            }
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="6" type="submit">' . KLocaleService::t('retry') . "</button>";
        }
    }
    private function _step7(SingleElementResultSet $data)
    {
        $parameterService = ParameterService::instance();
        $h = $parameterService->getHostName();
        $u = $parameterService->getUserName();
        $p = $parameterService->getPassword();
        $d = $parameterService->getDbName();
        $pr = $parameterService->getPrefixParam();
        $ru = $parameterService->getRedisUri();
        $this->_title = KLocaleService::t('step7.title');
        $this->_content .= '<table class="table">';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.host') . '</th>
            <td><input name="hostname" tabindex="1" type="text" class="form-control" value="' . ($h ?: 'localhost') . '" /></td></tr>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.db') . '</th>
            <td><input name="dbname" tabindex="2" type="text" class="form-control" value="' . ($d ?: '') . '" />
            <input type="checkbox" name="create" id="create" value="1"><label for="create">&nbsp;' . KLocaleService::t('step7.create_db') . '</label></td></tr>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.username') . '</th>
            <td><input name="username" tabindex="3" type="text" class="form-control" value="' . ($u ?: '') . '" /></td></tr>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.password') . '</th>
            <td><input name="password" tabindex="4" type="text" class="form-control" value="' . ($p ?: '') . '" /></td></tr>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.prefix') . '</th>
            <td><input name="prefix" tabindex="5" type="text" class="form-control" value="' . ($pr ?: 'keitaro_') . '" /></td></tr>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step7.redis_uri') . '</th>
            <td><input name="redis-uri" tabindex="6" type="text" class="form-control" value="' . ($ru ?: '127.0.0.1:6379/1') . '" /></td></tr>';
        $this->_content .= '</table>';
        if ($data->getStatus()) {
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="7" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            $this->_footer .= '<div class="status-error">' . $data->getErrorText() . '</div>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="7" type="submit">' . KLocaleService::t('retry') . '</button>';
        }
    }
    private function _step8(SingleElementResultSet $data)
    {
        $this->_title = KLocaleService::t('step8.title');
        if ($data->getStatus()) {
            $this->_content .= KLocaleService::t('step8.import_success');
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="9" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            $this->_footer .= '<div class="status-error">' . $data->getErrorText() . '</div>';
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="7" type="submit">' . KLocaleService::t('retry') . '</button>';
        }
    }
    private function _step9(ElementArrayResultSet $data)
    {
        $login = ParameterService::instance()->getKeitaroLogin();
        $pass = ParameterService::instance()->getKeitaroPass();
        $passConfirm = ParameterService::instance()->getKeitaroPassConfirm();
        $language = ParameterService::instance()->getLanguage();
        $extra = ParameterService::instance()->getExtraUrl();
        $timezone = ParameterService::instance()->getTimezone();
        $this->_title = KLocaleService::t('step9.title');
        $this->_content .= '<table class="table">';
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.login') . '</th>
            <td><input name="login" type="text" class="form-control" value="' . ($login ? $login : 'admin') . '" /></td>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.password') . '</th>
            <td><input name="password" type="password" class="form-control" value="' . ($pass ? $pass : '') . '" /></td>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.confirm_password') . '</th>
            <td><input name="password-confirm" type="password" class="form-control" value="' . ($passConfirm ? $passConfirm : '') . '" /></td>';
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.extra_url') . '</th>
            <td><input name="extra-url" type="text" class="form-control" value="' . ($extra ? $extra : '') . '" />
            <p class="description smaller">' . KLocaleService::t('step9.extra_url_description') . '</p>
            </td>';
        $selectedStorage = ParameterService::instance()->getCacheStorage();
        $avaliableStorages = StatusCheckerService::instance()->getAvaliableCacheStorages();
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.cache_storage') . '</th>
            <td>';
        foreach ($avaliableStorages as $storage) {
            $this->_content .= '<label><input name="cache_storage" type="radio" value="' . $storage[0] . '" ' . ($selectedStorage == $storage[0] ? 'checked' : '') . ' /> ' . $storage[1] . '</label>&nbsp;&nbsp;&nbsp;';
        }
        $this->_content .= '</td>';
        $draft = ParameterService::instance()->getDraftStorage();
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.draft_storage') . '</th>
            <td>
            <label><input name="draft_storage" type="radio" value="file" ' . ($draft == 'file' ? 'checked' : '') . ' /> Files</label>&nbsp;&nbsp;&nbsp;
            <label><input name="draft_storage" type="radio" value="db" ' . ($draft == 'db' ? 'checked' : '') . ' /> MySQL</label>&nbsp;&nbsp;&nbsp;
            <label><input name="draft_storage" type="radio" value="redis" ' . ($draft == 'redis' ? 'checked' : '') . ' /> Redis</label>
            </td>';
        $prefLanguage = KLocaleService::getPreferredLanguage();
        if ($language) {
            $prefLanguage = $language;
        }
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.ui_language') . '</th>
            <td>
            <label><input name="language" type="radio" value="ru" ' . ($prefLanguage == 'ru' ? 'checked' : '') . ' /> русский</label>&nbsp;&nbsp;&nbsp;
            <label><input name="language" type="radio" value="en" ' . ($prefLanguage == 'en' ? 'checked' : '') . ' /> english</label>
            </td>';
        $currency = ParameterService::instance()->getCurrency();
        if (!$currency) {
            if ($prefLanguage == 'en') {
                $currency = 'USD';
            } else {
                $currency = 'RUB';
            }
        }
        $this->_content .= '<tr><th>' . KLocaleService::t('step9.ui_currency') . '</th>
            <td>
            <label><input name="currency" type="radio" value="RUB" ' . ($currency == 'RUB' ? 'checked' : '') . ' /> RUB (&#8381)</label>&nbsp;&nbsp;&nbsp;
            <label><input name="currency" type="radio" value="USD" ' . ($currency == 'USD' ? 'checked' : '') . ' /> USD ($)</label>&nbsp;&nbsp;&nbsp;
            <label><input name="currency" type="radio" value="EUR" ' . ($currency == 'EUR' ? 'checked' : '') . ' /> EUR (€)</label>&nbsp;&nbsp;&nbsp;
            <label><input name="currency" type="radio" value="GBP" ' . ($currency == 'GBP' ? 'checked' : '') . ' /> GBP (£)</label>&nbsp;&nbsp;&nbsp;
            <label><input name="currency" type="radio" value="UAH" ' . ($currency == 'UAH' ? 'checked' : '') . ' /> UAH (₴)</label>
            </td>';
        if (!ConfigService::instance()->isKeitaro7()) {
            $this->_content .= '<tr><th>' . KLocaleService::t('step9.ui_timezone') . '</th>
            <td>
            <select name="timezone">';
            if (empty($timezone)) {
                $timezone = ConfigService::instance()->getDefaultTimezone();
            }
            foreach (ConfigService::instance()->getTimezones() as $key => $value) {
                $this->_content .= '<option value="' . $value . '" ' . ($value == $timezone ? 'selected' : '') . '>' . $key . '</option>';
            }
            $this->_content .= '</select>
            </td>';
        }
        $this->_content .= '</table>';
        if ($data->getStatus()) {
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="9" type="submit">' . KLocaleService::t('continue') . '</button>';
        } else {
            foreach ($data->getErrorTexts() as $text) {
                $this->_footer .= '<div class="status-error">' . $text . '</div>';
            }
            $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="9" type="submit">' . KLocaleService::t('retry') . '</button>';
        }
    }
    private function _step10($data)
    {
        $this->_title = KLocaleService::t('step10.title');
        $content = KLocaleService::t('step10.instruction');
        $content = str_replace('@document_root@', ConfigService::instance()->getDocumentRoot(), $content);
        $content = str_replace('@path@', ConfigService::instance()->getPath(), $content);
        $content = str_replace('@host@', $_SERVER['HTTP_HOST'], $content);
        $this->_content .= $content;
        $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="11" type="submit">' . KLocaleService::t('continue') . '</button>';
    }
    private function _step11($data)
    {
        $this->_title = KLocaleService::t('step11.title');
        $this->_content .= KLocaleService::t('step11.installation_complete');
        $this->_content .= '<ul class="list-unstyled">';
        $this->_content .= '<li><a href="?step=12">http://' . $_SERVER['HTTP_HOST'] . ConfigService::instance()->getPath() . '/admin</a></li>';
        $this->_content .= '<li>' . KLocaleService::t('step11.login') . ': ' . ParameterService::instance()->getPersistent('login') . '</li>';
        $this->_content .= '<li>' . KLocaleService::t('step11.password') . ': ' . ParameterService::instance()->getPersistent('password') . '</li>';
        $this->_content .= '</ul>';
        $this->_footer .= '<button autofocus class="btn btn-success" name="step" value="12" type="submit">' . KLocaleService::t('step11.start') . '</button>';
    }
    private function _step12($data)
    {
        header('LOCATION: ' . ConfigService::instance()->getPath() . '/admin/');
    }
    private function _wrapSuccess($text)
    {
        return '<div class="status-success"><i class="glyphicon glyphicon-ok-circle"></i> ' . $text . '</div>';
    }
    private function _wrapError($text)
    {
        return '<div class="status-error"><i class="glyphicon glyphicon-ban-circle"></i> ' . $text . '</div>';
    }
    private function renderPage($step)
    {
        $content = self::MAIN_TEMPLATE;
        $content = str_replace('#DOMAIN#', ConfigService::SITENAME, $content);
        $content = str_replace('#CONTENT#', $this->_content, $content);
        $content = str_replace('#FOOTER#', $this->_footer, $content);
        $content = str_replace('#STEP#', (int) $step, $content);
        $content = str_replace('#TITLE#', $this->_title, $content);
        $content = str_replace('#MAIN_TITLE#', KLocaleService::t('main_title'), $content);
        $content = str_replace('#DOCUMENTATION#', KLocaleService::t('documentation'), $content);
        $content = str_replace('#CURRENT_STEP#', (int) $step, $content);
        $content = str_replace('#ANOTHER_LANG#', $this->_getAnotherLang(), $content);
        $content = str_replace('#CHANGE_LANG#', $this->_getChangeLangText(), $content);
        $content = str_replace('#WIKI#', $this->_getWikiDomain(), $content);
        echo $content;
    }
    private function _getWikiDomain()
    {
        if (KLocaleService::getPreferredLanguage() == 'ru') {
            return 'help.keitaro.io/ru/installation';
        } else {
            return 'help.keitaro.io/en/installation';
        }
    }
    private function _getAnotherLang()
    {
        if (KLocaleService::getPreferredLanguage() == 'ru') {
            return 'en';
        } else {
            return 'ru';
        }
    }
    private function _getChangeLangText()
    {
        if (KLocaleService::getPreferredLanguage() == 'ru') {
            return 'RU → EN';
        } else {
            return 'EN → RU';
        }
    }
    public function renderStepPage($step, $data)
    {
        $action = '_step' . $step;
        $this->{$action}($data);
        $this->renderPage($step);
    }
    public function renderLockedPage()
    {
        $this->_title = KLocaleService::t('installation_locked');
        $this->_content = KLocaleService::t('remove_lock');
        $this->renderPage(1);
    }
}
}

namespace Controllers {
use Services\ParameterService;
use Views\ViewInterface;
use Models\StepModelInterface;
use Services\KLocaleService;
class InstallController
{
    private $_view = null;
    private $_model = null;
    public function __construct(ViewInterface $view, StepModelInterface $model)
    {
        $this->_view = $view;
        $this->_model = $model;
    }
    private function _checkLang()
    {
        if (isset($_GET['lang'])) {
            KLocaleService::setPreferredLanguage($_GET['lang']);
        }
    }
    public function dispatchSingle()
    {
        $this->_checkLang();
        if (!defined('CHECKER') && file_exists('var/install.lock')) {
            $this->_view->renderLockedPage();
        } else {
            $step = $this->_model->getCurrentStep();
            $data = $this->_model->getCurrentStepData();
            $this->_view->renderStepPage($step, $data);
        }
    }
    public function dispatchChain()
    {
        if (file_exists('var/install.lock')) {
            $this->_view->renderLockedPage();
        } else {
            while (true) {
                $step = $this->_model->getCurrentStep();
                $data = $this->_model->getCurrentStepData();
                if ($data === false) {
                    continue;
                }
                $newStep = $this->_view->renderStepPage($step, $data);
                $this->_model->updateStep($newStep);
            }
        }
    }
}
}

namespace Services {
class AbstractService
{
    protected static $_instances = array();
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(static::$_instances[$className])) {
            static::$_instances[$className] = new $className();
        }
        return static::$_instances[$className];
    }
    public static function isInitialized()
    {
        $className = get_called_class();
        return isset(static::$_instances[$className]);
    }
    public static function reset()
    {
        $className = get_called_class();
        static::$_instances[$className] = null;
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class IdentityService extends AbstractService
{
    public function getKey()
    {
        return file_get_contents('./var/license/key.lic');
    }
    public function getIp()
    {
        $settingsIp = ParameterService::instance()->getIp();
        if ($settingsIp) {
            return $settingsIp;
        }
        $resolveMethod = $this->getResolveMethod();
        if ($this->_isValid($resolveMethod)) {
            return $resolveMethod;
        }
        return call_user_func(array($this, '_get' . ucfirst($resolveMethod) . 'Ip'));
    }
    private function _isValid($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if (strpos($ip, '127.') === 0) {
            return false;
        }
        return strpos($ip, '192.168.') !== 0;
    }
    public function getResolveMethod()
    {
        if ($ip = ParameterService::instance()->getPersistent(ParameterService::IP)) {
            $resolveMethod = $ip;
        } else {
            $resolveMethod = RESOLVE_METHOD;
        }
        if ($this->_isValid($resolveMethod)) {
            return $resolveMethod;
        }
        if ($resolveMethod == 'domain') {
            $order = array('domain', 'server');
        } else {
            $order = array('server', 'domain');
        }
        $ip = null;
        foreach ($order as $method) {
            $ip = call_user_func(array($this, '_get' . ucfirst($method) . 'Ip'));
            if ($this->_isValid($ip)) {
                return $method;
            }
        }
        return 'domain';
    }
    private function _getServerIp()
    {
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $ip = str_replace('::ffff:', '', $ip);
        return $ip;
    }
    private function _getDomainIp()
    {
        return gethostbyname(@$_SERVER['HTTP_HOST']);
    }
    public function saveLicense($licenseKey)
    {
        if (!is_dir('./var')) {
            mkdir('./var', 0777);
        }
        if (!is_dir('./var/license')) {
            mkdir('./var/license', 0777);
        }
        if (!is_dir('./var/license/')) {
            return new GeneralResult(false, KLocaleService::t('step2.permission_error'));
        }
        file_put_contents('./var/license/key.lic', $licenseKey);
        return new GeneralResult(true, "");
    }
    public function validateLicense($key)
    {
        $ip = $this->getIp();
        $url = ConfigService::SITENAME . '/license/api/check?key=' . urlencode($key) . '&ip=' . $ip;
        $result = DownloadService::instance()->request($url);
        if (!$result->getStatus()) {
            return $result;
        }
        if (trim($result->getDesc()) == 'success') {
            return new GeneralResult(true, "");
        }
        return new GeneralResult(true, "");
    }
}
}

namespace Services {
class ParameterService extends AbstractService
{
    const DOMAIN = 'cliDomain';
    const IP = 'cliIp';
    const KEY = 'cliKey';
    const DB_HOST = 'cliDbHost';
    const DB_PREFIX = 'cliDbPrefix';
    const DB_USER = 'cliDbUser';
    const DB_NAME = 'cliDbName';
    const DB_PASS = 'cliDbPass';
    const REDIS_URI = 'cliRedisUri';
    const ADMIN_LOGIN = 'cliAdminLogin';
    const ADMIN_PASS = 'cliAdminPass';
    const ADMIN_PASS_CONFIRM = 'cliAdminPassConfirm';
    const EXTRA_URL = 'cliExtraUrl';
    const DRAFT_STORAGE = 'cliDraftStorage';
    const CACHE_STORAGE = 'cliCacheStorage';
    const LANGUAGE = 'cliLanguage';
    const CURRENCY = 'cliCurrency';
    const VERSION = 'cliVersion';
    const TIMEZONE = 'cliTimezone';
    const FORCE_TOKUDB = 'cliForceTokuDb';
    const CUSTOM_PACKAGE = 'customPackage';
    const VERIFY = 'verify';
    const STABILITY_CHANNEL = 'unstable';
    const UNSTABLE = 'unstable';
    private $_cliParams = array();
    private function isCli()
    {
        return php_sapi_name() == 'cli' && !defined("PRELOADER");
    }
    private function setCliParam($key, $val)
    {
        $this->_cliParams[$key] = $val;
    }
    private function getCliParam($key)
    {
        if (isset($this->_cliParams[$key])) {
            return $this->_cliParams[$key];
        } else {
            return false;
        }
    }
    private function getPostParam($name)
    {
        if (!empty($_POST[$name])) {
            return $_POST[$name];
        } else {
            return false;
        }
    }
    private function saveInSession($key, $val)
    {
        $_SESSION[$key] = $val;
    }
    private function getFromSession($key)
    {
        return @$_SESSION[$key];
    }
    public function getIp()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::IP);
        } else {
            return false;
        }
    }
    public function getLicense()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::KEY);
        } else {
            return $this->getPostParam('licenseKey');
        }
    }
    public function getMajorVersionNumber()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::VERSION);
        } else {
            return $this->getPostParam('version');
        }
    }
    public function getHostName()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::DB_HOST);
        } else {
            return $this->getPostParam('hostname');
        }
    }
    public function getUserName()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::DB_USER);
        } else {
            return $this->getPostParam('username');
        }
    }
    public function getPassword()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::DB_PASS);
        } else {
            return $this->getPostParam('password');
        }
    }
    public function getDbName()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::DB_NAME);
        } else {
            return $this->getPostParam('dbname');
        }
    }
    public function getCreateDatabaseParam()
    {
        if ($this->isCli()) {
            return false;
        } else {
            return $this->getPostParam('create');
        }
    }
    public function getPrefixParam()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::DB_PREFIX);
        } else {
            return $this->getPostParam('prefix');
        }
    }
    public function getRedisUri()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::REDIS_URI);
        } else {
            return $this->getPostParam('redis-uri');
        }
    }
    public function getKeitaroLogin()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::ADMIN_LOGIN);
        } else {
            return $this->getPostParam('login');
        }
    }
    public function getKeitaroPass()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::ADMIN_PASS);
        } else {
            return $this->getPostParam('password');
        }
    }
    public function getStabilityChannel()
    {
        return self::STABILITY_CHANNEL;
    }
    public function getKeitaroPassConfirm()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::ADMIN_PASS_CONFIRM);
        } else {
            return $this->getPostParam('password-confirm');
        }
    }
    public function getExtraUrl()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::EXTRA_URL);
        } else {
            return $this->getPostParam('extra-url');
        }
    }
    public function getLanguage()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::LANGUAGE);
        } else {
            return $this->getPostParam('language');
        }
    }
    public function getCurrency()
    {
        if ($this->isCli()) {
            return $this->getCliParam(self::CURRENCY);
        } else {
            return $this->getPostParam('currency');
        }
    }
    public function getCacheStorage()
    {
        if ($this->isCli()) {
            $cache = $this->getCliParam(self::CACHE_STORAGE);
        } else {
            $cache = $this->getPostParam('cache_storage');
        }
        if (!$cache) {
            $cache = 'files';
        }
        return $cache;
    }
    public function getDraftStorage()
    {
        if ($this->isCli()) {
            $draft = $this->getCliParam(self::DRAFT_STORAGE);
        } else {
            $draft = $this->getPostParam('draft_storage');
        }
        if (!$draft) {
            $draft = 'file';
        }
        return $draft;
    }
    public function getTimezone()
    {
        if ($this->isCli()) {
            $timezone = $this->getCliParam(self::TIMEZONE);
        } else {
            $timezone = $this->getPostParam('timezone');
        }
        return $timezone;
    }
    public function isForceTokuDb()
    {
        if ($this->isCli()) {
            $forceTokuDb = $this->getCliParam(self::FORCE_TOKUDB);
        } else {
            $forceTokuDb = false;
        }
        return $forceTokuDb;
    }
    public function savePersistent($key, $val)
    {
        if ($this->isCli()) {
            $this->setCliParam($key, $val);
        } else {
            $this->saveInSession($key, $val);
        }
    }
    public function getPersistent($key)
    {
        if ($this->isCli()) {
            return $this->getCliParam($key);
        } else {
            return $this->getFromSession($key);
        }
    }
    public function isVerifyMode()
    {
        return $this->getCliParam(self::VERIFY);
    }
    public function removeAllPersistent()
    {
        session_unset();
    }
    public function getCustomPackage()
    {
        return $this->getCliParam(self::CUSTOM_PACKAGE);
    }
}
}

namespace ResultSets {
interface ResultSetInterface
{
    public function getStatus();
}
}

namespace ResultSets {
use Services\ConfigService;
use Services\IdentityService;
use Services\KLocaleService;
use Services\StatusCheckerService;
class Step1ResultSet implements ResultSetInterface
{
    const HEADER_COL = 0;
    const TEXT_COL = 1;
    const STATUS_COL = 2;
    public $ip = null;
    public $php = null;
    public $mysql = null;
    public $safemode = null;
    public $xcache = null;
    public $iconv = null;
    public $mbstring = null;
    public $zlib = null;
    public $curl = null;
    public $permissions = null;
    public $ioncube = null;
    public $zip = null;
    public $json = null;
    public $hash = null;
    public function getStatus()
    {
        $result = true;
        foreach ($this as $key => $value) {
            if (!$value->getStatus()) {
                $result = false;
            }
        }
        return $result;
    }
    public function getMemberRow($member)
    {
        $result = array();
        $result[self::HEADER_COL] = KLocaleService::t('step1.' . $member);
        $result[self::TEXT_COL] = $this->{$member}->getDesc();
        $result[self::STATUS_COL] = $this->{$member}->getStatus();
        return $result;
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class StatusCheckerService extends AbstractService
{
    const IONCUBE_NOT_INSTALLED = 1;
    const IONCUBE_OLD_VERSION = 2;
    const IONCUBE_INSTALLED = 3;
    public static function checkMysqlStatus()
    {
        if (!extension_loaded('pdo_mysql')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkPhpVersion()
    {
        if (version_compare(phpversion(), ConfigService::MIN_PHP_VERSION) > 0) {
            return new GeneralResult(true, phpversion());
        } else {
            return new GeneralResult(false, phpversion() . ' - ' . KLocaleService::t('step1.no_php5'));
        }
    }
    public static function checkXcacheStatus()
    {
        if (extension_loaded('xcache')) {
            return new GeneralResult(false, KLocaleService::t('step1.xcache_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.not_installed'));
        }
    }
    public static function checkIconvStatus()
    {
        if (!function_exists('iconv')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkCurlStatus()
    {
        if (!function_exists('curl_init')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkZlibStatus()
    {
        if (!function_exists('gzopen') && !function_exists('gzopen64')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkDirStatus()
    {
        if (!is_writeable('./')) {
            return new GeneralResult(false, KLocaleService::t('step1.dir_isnt_writable'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.dir_is_writable'));
        }
    }
    public static function checkSafeModeStatus()
    {
        if (ini_get('safe_mode') == 1 || ini_get('safe_mode') === 'on') {
            return new GeneralResult(false, KLocaleService::t('step1.safe_mode_enable'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.safe_mode_disable'));
        }
    }
    public static function checkMbStatus()
    {
        if (!function_exists('mb_internal_encoding')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkIpStatus($ip)
    {
        if (strpos($ip, '127.') === 0 || strpos($ip, '192.168.') === 0) {
            return new GeneralResult(false, '<strong>' . $ip . '</strong> — ' . KLocaleService::t('step1.should_be_external'));
        } else {
            return new GeneralResult(true, $ip);
        }
    }
    public static function checkIoncubeStatus()
    {
        if (!extension_loaded('ionCube Loader')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed') . ' — <a href="https://help.keitaro.io/en/ioncube">' . KLocaleService::t('step1.ioncube_installation') . '</a>');
        } else {
            $version = ioncube_loader_version();
            if (version_compare(ConfigService::MIN_IONCUBE_VERSION, $version) <= 0) {
                return new GeneralResult(true, KLocaleService::t('step1.installed'));
            } else {
                return new GeneralResult(false, ioncube_loader_version() . ' — ' . KLocaleService::t('step1.min_version') . ' ' . ConfigService::MIN_IONCUBE_VERSION);
            }
        }
    }
    public static function checkRedisStatus()
    {
        if (!class_exists('Redis')) {
            return new GeneralResult(false, "");
        } else {
            return new GeneralResult(true, "");
        }
    }
    public static function checkSqliteStatus()
    {
        if (!extension_loaded('pdo_sqlite')) {
            return new GeneralResult(false, "");
        } else {
            return new GeneralResult(true, "");
        }
    }
    public static function checkMemcacheStatus()
    {
        if (!function_exists("memcache_connect")) {
            return new GeneralResult(false, "");
        } else {
            return new GeneralResult(true, "");
        }
    }
    public static function checkZipStatus()
    {
        if (!extension_loaded('zip')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkJsonStatus()
    {
        if (!function_exists('json_encode')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public static function checkHashStatus()
    {
        if (!function_exists('hash')) {
            return new GeneralResult(false, KLocaleService::t('step1.not_installed'));
        } else {
            return new GeneralResult(true, KLocaleService::t('step1.installed'));
        }
    }
    public function getAvaliableCacheStorages()
    {
        $storages = array(array("files", KLocaleService::t('step9.files')));
        if ($this->checkRedisStatus()->getStatus()) {
            $storages[] = array("redis", "Redis");
        }
        return $storages;
    }
}
}

namespace ResultSets {
class GeneralResult implements ResultSetInterface
{
    private $_status;
    private $_desc;
    public function __construct($status, $desc)
    {
        $this->_status = $status;
        $this->_desc = $desc;
    }
    public function getStatus()
    {
        return $this->_status;
    }
    public function getDesc()
    {
        return $this->_desc;
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class ConfigService extends AbstractService
{
    const SITENAME = 'https://keitaro.io';
    const MIN_IONCUBE_VERSION = 5;
    const MIN_PHP_VERSION = '7.0.0';
    private $cnf = array();
    private $versionPHP;
    public function getRemoteFiles()
    {
        $ip = IdentityService::instance()->getIp();
        $key = IdentityService::instance()->getKey();
        return array('GeoLiteCity' => array('path' => 'var/geoip/GeoLiteCity.dat', 'dataUrl' => 'https://keitaro.io/db/GeoLiteCity.dat.gz', 'crcUrl' => 'https://keitaro.io/db/GeoLiteCity.dat.crc'), 'SxGeoCity' => array('path' => 'var/geoip/SxGeoCity.dat', 'dataUrl' => 'https://keitaro.io/db/SxGeoCity.gz', 'crcUrl' => 'https://keitaro.io/db/SxGeoCity.crc'), 'IP2LOCATION-LITE-DB3' => array('path' => 'var/geoip/IP2Location/lite/IP2LOCATION-LITE-DB3.BIN', 'dataUrl' => 'https://keitaro.io/geoip/IP2LOCATION-LITE-DB3.BIN.gz', 'crcUrl' => 'https://keitaro.io/geoip/IP2LOCATION-LITE-DB3.BIN.crc'), 'operatorsV1' => array('path' => 'var/operators/operators.v1.dat', 'dataUrl' => 'https://keitaro.io/db/operators.v1.dat.gz', 'crcUrl' => 'https://keitaro.io/db/operators.v1.dat.crc'), 'operatorsV2' => array('path' => 'var/operators/operators.v2.dat', 'dataUrl' => 'https://keitaro.io/db/operators.v2.dat.gz', 'crcUrl' => 'https://keitaro.io/db/operators.v2.dat.crc'), 'bots' => array('path' => 'var/bots/bots.dat', 'dataUrl' => 'https://keitaro.io/db/bots.dat.gz', 'crcUrl' => 'https://keitaro.io/db/bots.dat.crc'));
    }
    public function getDocumentRoot()
    {
        return dirname(__FILE__);
    }
    public function updateConfigFile($host, $user, $pass, $db, $prefix, $redisUri)
    {
        $salt = '';
        $config = file_get_contents($this->getConfigIniSamplePath());
        if (file_exists($this->getConfigIniPath())) {
            $oldCnf = parse_ini_string(file_get_contents($this->getConfigIniPath()), true);
            if ($oldCnf['system']['salt']) {
                $salt = $oldCnf['system']['salt'];
            }
        }
        if (!$salt) {
            $salt = uniqid(mt_rand(), true);
        }
        $config = str_replace('#type#', 'mysql', $config);
        $config = str_replace('#host#', $host, $config);
        $config = str_replace('#login#', $user, $config);
        $config = str_replace('#password#', $pass, $config);
        $config = str_replace('#dbname#', $db, $config);
        $config = str_replace('#prefix#', $prefix, $config);
        $config = str_replace('#salt#', $salt, $config);
        $config = str_replace('#resolve_method#', IdentityService::instance()->getResolveMethod(), $config);
        $config = preg_replace('/\\nuri\\s+=.*\\n/', "\nuri = {$redisUri}\n", $config);
        if (!file_put_contents($this->getConfigIniPath(), $config)) {
            return new GeneralResult(false, KLocaleService::t('step7.config_permission_error'));
        } else {
            return new GeneralResult(true, "");
        }
    }
    public function getConfigIniSamplePath()
    {
        return 'application/config/config.ini.sample';
    }
    public function getConfigIniPath()
    {
        return 'application/config/config.ini.php';
    }
    public function updateIniConfig()
    {
        $this->cnf = parse_ini_string(file_get_contents($this->getConfigIniPath()), true);
    }
    public function getPath()
    {
        $tmp = explode('/', $_SERVER["REQUEST_URI"]);
        unset($tmp[count($tmp) - 1]);
        return implode('/', $tmp);
    }
    public function getIniConfig()
    {
        return $this->cnf;
    }
    public function updateIniStore()
    {
        $config = file_get_contents($this->getConfigIniPath());
        if (!@file_put_contents($this->getConfigIniPath(), $config)) {
            return new GeneralResult(false, KLocaleService::t('step7.config_permission_error'));
        } else {
            return new GeneralResult(true, "");
        }
    }
    public function isKeitaro7()
    {
        $version = $this->getVersionPHP();
        return $version[0] <= 7;
    }
    public function getVersionPHP()
    {
        $versionFileName = getcwd() . '/version.php';
        if (is_file($versionFileName)) {
            $this->versionPHP = (require $versionFileName);
        } else {
            return null;
        }
        return $this->versionPHP;
    }
    public function isKeitaro9()
    {
        $version = $this->getVersionPHP();
        return $version[0] == 9;
    }
    public function getTimezones()
    {
        $timezones = [];
        foreach (\DateTimeZone::listIdentifiers() as $tz) {
            $time = new \DateTime('now', new \DateTimeZone($tz));
            $name = '(GMT' . $time->format('P') . ') ' . $tz;
            $timezones[$name] = $tz;
        }
        ksort($timezones);
        return $timezones;
    }
    public function getDefaultTimezone()
    {
        return date_default_timezone_get();
    }
    public function isValidTimezone($timezone)
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }
    public function getUTCTime()
    {
        return gmdate('Y-m-d H:i:s');
    }
}
}

namespace Services {
class KLocaleService extends AbstractService
{
    protected static $_data = array('ru' => array('main_title' => 'Установка Keitaro', 'step1' => array('title' => 'Проверка конфигурации сервера', 'mysql' => 'Наличие PDO MySQL', 'safemode' => 'Отключенный Safemode', 'ioncube' => 'IonCube Loader', 'php' => 'Минимальная 7.0', 'iconv' => 'Расширение iconv', 'mbstring' => 'Расширение <a href="http://php.net/manual/en/mbstring.installation.php">mbstring</a>', 'curl' => 'Расширение CURL', 'permissions' => 'Доступ к текущей директории', 'ip' => 'IP сервера', 'min_version' => 'Для Keitaro 7 необходима версия 5.0 или выше', 'success' => '<p><b>Проверка прошла успешно.</b></p><p>Вы можете установить Keitaro на этот сервер</p>', 'failure' => 'Сервер не соответствует требованиям', 'retry' => 'Проверить еще раз', 'not_installed' => 'Не установлено', 'installed' => 'Установлено', 'no_php5' => 'необходим PHP 7.0 или новее', 'dir_isnt_writable' => 'Нет прав на запись — установите права на запись папки с инсталятором', 'dir_is_writable' => 'Запись разрешена', 'safe_mode_enable' => 'Включен — необходимо отключить', 'safe_mode_disable' => 'Отключен', 'should_be_external' => 'необходим внешний IP', 'ioncube_installation' => 'инструкция по установке', 'storage_recommend' => 'Рекомендуется установить APC или Memcache (не обязательно)', 'zlib' => 'Расширение <a href="http://php.net/manual/en/zlib.installation.php">zlib</a>', 'xcache' => 'Xcache', 'xcache_installed' => 'Включен. Наличие xcache может привести к периодическим появлениям ошибки "502 Bad Gateway".', 'zip' => 'Расширение <a href="http://php.net/manual/ru/zip.installation.php">Zip</a>', 'json' => 'Расширение <a href="http://php.net/manual/ru/json.installation.php">JSON</a>', 'hash' => 'Расширение <a href="http://php.net/manual/ru/hash.installation.php">Hash</a>'), 'step2' => array('title' => 'Проверка лицензии', 'permission_error' => 'Скрипт не смог создать папку.', 'ip' => 'Лицензия должна быть привязана к IP ', 'license_error' => 'Ключ не подходит к этому серверу или лицензия привязана к другому IP', 'enter_key' => 'Введите ваш лицензионный ключ:', 'check' => 'Проверить'), 'step3' => array('title' => 'Загрузка файлов', 'delete_www' => 'Удалите папку "www" из директории TDS', 'choose_version' => 'Выберите версию', 'description' => '<p>Процедура загрузки дистрибутива может занять до 60 секунд. Пожалуйста, не останавливайте загрузку страницы и не закрывайте браузер до завершения.</p>
                        <p>Если после загрузки появилась пустая страница или сообщение о недоступной странице, тогда перейдите на <a href="?step=5">следующий этап самостоятельно</a>.</p>
                        <p>&nbsp;</p>', 'warning' => '<p><b>Внимание!</b> При распаковке будут заменены index.php и .htaccess в текущей директории. </p>', 'has7warning' => '<b>Внимание!</b> Найдена установленная Keitaro 7. Для обновления 7 версии на 8 используйте <a href="https://help.keitaro.io/ru/upgrade8">https://help.keitaro.io/ru/upgrade8</a>', 'start' => 'Начать загрузку', 'ignore_index' => 'Заменить index.php', 'outdated' => '(устаревшая)'), 'step4' => array('title' => 'Загрузка файлов', 'connection_error' => 'Не удалось подключиться к серверу.', 'permission_error' => 'Не удалось записать файл. Установите права 777 папку с инсталятором.', 'tools_error' => 'На сервере нет инструментов для скачивания файлов. Дальнейшая установка невозможна.', 'success' => 'Архив с Keitaro загружен. Дальше последует распаковка файлов.', 'unpacked' => 'Архив с Keitaro распакован.', 'unpack_error' => 'Не удалось распаковать файлы. Убедитесь, что скрипту разрешена запись в текущую дируторию и свободного места не менее 30мб.', 'download_error' => 'Не удалось загрузить файлы.
                    Распакуйте самостоятельно <a target="_blank" href="https://keitaro.io/package/package2.tar.gz">https://keitaro.io/package/package2.tar.gz</a>, затем нажмите на кнопку продолжения', 'curl_error' => 'curl_exec отключена в настройках PHP'), 'step5' => array('title' => 'Подключение внешних баз', 'description' => 'Необходима загрузка внешних баз: geoip, операторов, ботов.'), 'step6' => array('title' => 'Подключение внешних данных', 'success' => 'Все списки успешно подключены', 'cant_download_file' => 'Не удалось загрузить файл', 'crc32_failed' => 'Загруженный файл оказался поврежден. Попробуйте повторить загрузку.', 'crc32_remote_empty' => 'Контрольная сумма на сервере равна нулю.', 'crc32_local_empty' => 'Контрольная сумма скачанного файла равна нулю.'), 'step7' => array('title' => 'Настройки доступа к базе данных', 'host' => 'Хост', 'db' => 'Имя БД', 'username' => 'Имя пользователя', 'password' => 'Пароль', 'create_db' => 'создать базу', 'prefix' => 'Префикс таблиц', 'redis_uri' => 'URI подключения к Redis', 'cant_create' => 'Не удалось создать базу данных', 'db_error' => 'Не удалось подключиться к базе данных', 'connection_error' => 'Не удалось подключиться к базе данных', 'config_permission_error' => 'Нет прав на запись в папке application/config.'), 'step8' => array('title' => 'Импорт структуры и данных в базу данных', 'import_success' => 'Данные успешно импортированы.', 'import_failure' => 'При импорте данных возникла ошибка.'), 'step9' => array('title' => 'Авторизация и настройки', 'login' => 'Логин администратора', 'password' => 'Пароль', 'confirm_password' => 'Повторите пароль', 'extra_url' => 'Редирект с домена', 'extra_url_description' => 'Сейчас вы можете прописать любой адрес. Например, https://google.com. Позже сможете сменить в настройках.', 'login_error' => 'Введите логин.', 'password_error' => 'Введите пароль.', 'passwords_mismatch' => 'Пароли не совпадают.', 'extra_url_error' => 'Введите запасной URL.', 'extra_url_incorrect' => 'URL должен начинаться с http:// или https://', 'ui_language' => 'Язык интерфейса', 'ui_currency' => 'Валюта', 'ui_timezone' => 'Временная зона интерфейса', 'draft_storage' => 'Хранилище для обработчика трафика', 'draft_no_redis' => 'Redis не найден', 'cache_storage' => 'Кэширование', 'files' => 'Файлы', 'no_cache' => 'Не кэшировать'), 'step10' => array('title' => 'Добавление задания в CRON', 'instruction' => '
                <p>Необходимо добавить задачу для планировщика (Cron\'а). Это необходимо для работы обработчика статистики.</p>
                <p>Планировщик задач имеется во всех панелях управления сервером.</p>
                <p>&nbsp;</p>
                <p>Интервал выполнения: <code>каждую минуту</code>
                <p>Команда: выберите один из вариантов ниже.
                <p>&nbsp;</p>
                <h4>Вариант 1: С использованием консольного PHP</h4>
                <p><code>php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Вариант 2: С использованием wget</h4>
                <p><code>wget -O /dev/null -q http://@host@@path@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Вариант 3: Строка для Crontab:</h4>
                <p>Выполните в консоли: <code>crontab -e -u www-data</code> (www-data — это имя пользователя в системе, главное не root!)
                <p>Вставьте <code>* * * * * php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Вариант 3: Строка для Crontab:</h4>
                <p>Выполните в консоли: <code>crontab -e</code>
                <p>Вставьте <code>* * * * * php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Timeweb.ru:</h4>
                <p>Команда для PHP 7.0 <code>/opt/php7.0/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 5.6 <code>/opt/php5.6/bin/php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Beget.ru:</h4>
                <p>Команда для PHP 7.1<code>/usr/local/php-cgi/7.1/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 7.0<code>/usr/local/php-cgi/7.0/bin/php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Reg.ru ISPManager:</h4>
                <p>Команда для PHP 7.1<code>/opt/php/7.1/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 7.0<code>/opt/php/7.0/bin/php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Fastvps.ru ISPManager:</h4>
                <p>Команда для PHP 7.1<code>/opt/php71/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 7.0<code>/opt/php70/bin/php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Sweb.ru:</h4>
                <p>Команда для PHP 7.1<code>php7.1 @document_root@/cron.php</code></p>
                <p>Команда для PHP 7.0<code>php7.0 @document_root@/cron.php</code></p>
                <p>Команда для PHP 5.6<code>php5.6 @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Jino.ru:</h4>
                <p>Команда для PHP 7.0<code>php7.0 @document_root@/cron.php</code></p>
                <p>Команда для PHP 5.6<code>php5.6 @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                
                <h4>ihc.ru:</h4>
                <p>Команда для PHP 7.1<code>/usr/local/php71/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 7.0<code>/usr/local/php70/bin/php @document_root@/cron.php</code></p>
                <p>Команда для PHP 5.6<code>/usr/local/php56/bin/php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <p>Инструкции для различных панелей управления и хостингов описаны на этой странице <a target="_blank" href="https://help.keitaro.io/ru/cron">https://help.keitaro.io/ru/cron</a></p>'), 'step11' => array('title' => 'Установка завершена!', 'installation_complete' => '<p>Поздравляем с успешным завершением установки Keitaro.</p>
                    <p>При возникновении каких-либо проблем или технических неполадок, обращайтесь в нашу техподдержку.</p>
                    <p>Сейчас файл install.php будет заблокирован, можете удалить его.</p><br />', 'start' => 'Перейти в админку', 'login' => 'Логин', 'password' => 'Пароль'), 'documentation' => 'Помощь', 'continue' => 'Продолжить', 'retry' => 'Повторить', 'installation_locked' => 'Установка заблокирована', 'remove_lock' => 'Для разблокировки удалите файл "/var/install.lock".'), 'en' => array('main_title' => 'Keitaro Installation', 'step1' => array('title' => 'Server Configuration', 'mysql' => 'PDO MySQL', 'safemode' => 'Safe-mode must be disabled', 'php' => 'PHP version 7.0 - 7.3', 'ioncube' => 'IonCube Loader', 'iconv' => 'Iconv extension', 'mbstring' => 'Mbstring extension', 'curl' => 'CURL extension', 'permissions' => 'Writable permissions to current directory', 'ip' => 'Server IP', 'min_version' => 'Keitaro 7 requires 5.0 o greater', 'success' => '<p><b>Checks done.</b></p> <p>You can install Keitaro!</p>', 'failure' => 'Please fix the issues and press "Retry"', 'retry' => 'Retry', 'not_installed' => 'Not installed', 'installed' => 'Installed', 'no_php5' => 'PHP 7.1 is recommended. PHP 5.6 is minimal.', 'dir_isnt_writable' => 'No writable permissions. Please set attributes for current directory to 775 or 777.', 'dir_is_writable' => 'Writable', 'safe_mode_enable' => 'Enabled. You must disable safe-mode.', 'safe_mode_disable' => 'Disabled', 'should_be_external' => 'IP must be external', 'ioncube_installation' => 'Instruction (RU)', 'storage_recommend' => 'APC or Memcache recommended', 'zlib' => '<a href="http://php.net/manual/en/zlib.installation.php">Zlib</a> extension', 'xcache' => 'Xcache', 'xcache_installed' => 'We recommend you to disable xcache extension.', 'zip' => '<a href="http://php.net/manual/en/zip.installation.php">Zip</a> extension', 'json' => '<a href="http://php.net/manual/en/json.installation.php">JSON</a> extension', 'hash' => '<a href="http://php.net/manual/en/hash.installation.php">Hash</a> extension'), 'step2' => array('title' => 'License Key', 'ip' => 'License must be assigned to IP', 'permission_error' => 'Unable to create folder to save license key.', 'license_error' => 'Key is invalid. Please check your license key and IP address', 'enter_key' => 'Please enter your license key', 'check' => 'Check'), 'step3' => array('title' => 'Install files', 'delete_www' => 'Please, remove folder "www" from current directory', 'choose_version' => 'Choose version', 'description' => '<p>Press "Continue" to download and unpack Keitaro.</p>', 'warning' => '<b>Warning!</b> Files that contains current directory will be replaced.', 'has7warning' => '<b>Warning!</b> Found installed Keitaro 7. For upgrade use <a href="https://help.keitaro.io/ru/upgrade8">https://help.keitaro.io/ru/upgrade8</a>', 'start' => 'Continue', 'ignore_index' => 'Replace index.php', 'outdated' => '(legacy)'), 'step4' => array('title' => 'Unpacking', 'connection_error' => 'Can\'t connect to the server. Please check firewall settings.', 'permission_error' => 'Can\'t unpack files. Please check permissions to current directory.', 'tools_error' => 'Unable to use any tools to download package.', 'success' => 'Keitaro has been downloaded', 'unpacked' => 'Keitaro has been unpacked', 'unpack_error' => 'Unable to unpack files. Current directory must have writable permissions and free space should be more than 30MB.', 'download_error' => 'Unable to download archive. Please, check your firewall settings.', 'curl_error' => 'curl_exec disabled in PHP settings'), 'step5' => array('title' => 'External Databases', 'description' => 'Press "Continue" to download external databases.'), 'step6' => array('title' => 'External Databases', 'success' => 'Files successfully unpacked!', 'cant_download_file' => 'Unable to download file ', 'crc32_failed' => 'Downloaded file is corrupted. Please press Retry.', 'crc32_remote_empty' => 'Checksum on the server is equals to zero.', 'crc32_local_empty' => 'Checksum of the downloaded file is equals to zero.'), 'step7' => array('title' => 'MySQL Settings', 'host' => 'Hostname', 'db' => 'DB name', 'username' => 'Username', 'password' => 'Password', 'create_db' => 'create database', 'prefix' => 'Table Prefix', 'redis_uri' => 'Redis URI', 'cant_create' => 'Unable to create database. Please create it from your server control panel', 'db_error' => 'Unable to connect to database. Please check username and password', 'connection_error' => 'Unable to connect to database', 'config_permission_error' => 'No writable permissions on application/config. Please set 775 or 777.'), 'step8' => array('title' => 'MySQL Schema Import', 'import_success' => 'DB schema has been successfully imported!', 'import_failure' => 'DB error occured during import.'), 'step9' => array('title' => 'Create User', 'login' => 'Admin Login', 'password' => 'Admin Password', 'confirm_password' => 'Repeat Password', 'extra_url' => 'Domain redirect', 'extra_url_description' => 'You can write "https://google.com" for now. Later yu can change it in the Setting page.', 'login_error' => 'Please enter login.', 'password_error' => 'Please enter password.', 'passwords_mismatch' => 'Passwords are not match.', 'extra_url_error' => 'Please enter Domain redirect.', 'extra_url_incorrect' => 'URL must contain http:// or https://', 'ui_language' => 'UI Language', 'ui_currency' => 'Currency', 'ui_timezone' => 'UI Timezone', 'draft_storage' => 'Storage for draft data', 'draft_no_redis' => 'Redis not found', 'cache_storage' => 'Cache Storage', 'files' => 'Files', 'no_cache' => 'Do not cache'), 'step10' => array('title' => 'Schedule Cron', 'instruction' => '
                <p>Now create new job to Cron.</p>
                <p>Interval: <code>every minute</code></p>
                <p>Command: choose one of the options below.</p>
                <p>&nbsp;</p>
                <h4>Option 1: For Shared Hosting (with PHP-CLI)</h4>
                <p>Command:</b> <code>php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <h4>Option 2: Dedicated and Virtual Servers (with Curl)</h4>
                <p><code>curl -s http://@host@@path@/cron.php > /dev/null</code></p>
                <p>&nbsp;</p>
                <h4>Option 3: Crontab:</h4>
                <p>Run <code>crontab -e</code></p>
                <p>And add <code>* * * * * php @document_root@/cron.php</code></p>
                <p>&nbsp;</p>
                <p>More information about cron please visit <a target="_blank" href="https://help.keitaro.io/en/cron">https://help.keitaro.io/en/cron</a>
                </p>'), 'step11' => array('title' => 'Installation Finished', 'installation_complete' => '<p>Thank you for choosing Keitaro.</p>
                    <p>If you have any questions about Keitaro, please contact us through website https://keitaro.io.</p>
                    <p>Now you can remove install.php.</p>
                    <br />', 'start' => 'Go to the control panel', 'login' => 'Login', 'password' => 'Password'), 'documentation' => 'Help', 'continue' => 'Continue', 'retry' => 'Retry', 'installation_locked' => 'Installation is locked', 'remove_lock' => 'If you want to unlock remove file "/var/install.lock".'));
    protected static $_language;
    protected static $_languages = array('ru', 'en');
    const DEFAULT_LANGUAGE = 'ru';
    public static function setPreferredLanguage($lang)
    {
        if (!in_array($lang, self::$_languages)) {
            throw new \Exception('Language ' . $lang . ' not exists');
        }
        if (!headers_sent()) {
            setcookie('installer_lang', $lang);
            $_COOKIE['installer_lang'] = $lang;
        }
    }
    public static function getPreferredLanguage()
    {
        $lang = self::DEFAULT_LANGUAGE;
        if (isset($_COOKIE['installer_lang'])) {
            $lang = $_COOKIE['installer_lang'];
        } elseif (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            if (strstr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 'en')) {
                $lang = 'en';
            }
            if (strstr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 'ru')) {
                $lang = 'ru';
            }
        }
        if (!in_array($lang, self::$_languages)) {
            $lang = self::DEFAULT_LANGUAGE;
        }
        return $lang;
    }
    public static function get($key, $params = null)
    {
        $path = explode('.', $key);
        $data = self::$_data[self::getPreferredLanguage()];
        $translation = self::_find($path, $data);
        if (!isset($translation)) {
            $translation = $key;
        }
        if ($params) {
            if (!is_array($params)) {
                $params = array($params);
            }
            $args = array_merge(array($translation), $params);
            $translation = call_user_func_array('sprintf', $args);
        }
        return $translation;
    }
    public static function t($key, $params = null)
    {
        return self::get($key, $params);
    }
    protected static function _find($path, $data)
    {
        $key = array_shift($path);
        if (!isset($data[$key])) {
            return null;
        }
        if (count($path)) {
            return self::_find($path, $data[$key]);
        }
        return $data[$key];
    }
}
}

namespace ResultSets {
use Services\IdentityService;
use Services\KLocaleService;
class SingleElementResultSet implements ResultSetInterface
{
    public $element = false;
    public function getStatus()
    {
        return $this->element->getStatus();
    }
    public function getErrorText()
    {
        return $this->element->getDesc();
    }
}
}

namespace ResultSets {
use Services\ConfigService;
use Services\IdentityService;
use Services\KLocaleService;
use Services\StatusCheckerService;
class Step3ResultSet implements ResultSetInterface
{
    public $www = null;
    public $index = null;
    public $has7Problem = null;
    public function getStatus()
    {
        return isset($this->www) && $this->www->getStatus();
    }
    public function getMemberRow($member)
    {
        $result = array();
        $result[self::HEADER_COL] = KLocaleService::t('step1.' . $member);
        $result[self::TEXT_COL] = $this->{$member}->getDesc();
        $result[self::STATUS_COL] = $this->{$member}->getStatus();
        return $result;
    }
}
}

namespace ResultSets {
use Services\IdentityService;
use Services\KLocaleService;
class ElementArrayResultSet implements ResultSetInterface
{
    private $_elements = array();
    public function addElement(GeneralResult $elem)
    {
        $this->_elements[] = $elem;
    }
    public function getStatus()
    {
        foreach ($this->_elements as $element) {
            if (!$element->getStatus()) {
                return false;
            }
        }
        return true;
    }
    public function getErrorTexts()
    {
        $result = array();
        foreach ($this->_elements as $element) {
            $result[] = $element->getDesc();
        }
        return $result;
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class DownloadService extends AbstractService
{
    private $stubbedRequests = [];
    public function stub($url, $result)
    {
        $this->stubbedRequests[$url] = $result;
    }
    public function request($url)
    {
        if (isset($this->stubbedRequests[$url])) {
            return $this->stubbedRequests[$url];
        }
        if (defined('PHPUNIT')) {
            throw new \Exception("External requests forbidden in testing env ({$url})");
        }
        if (function_exists('curl_init')) {
            if ($this->_isCurlDisabled()) {
                return new GeneralResult(false, KLocaleService::t('step4.curl_error'));
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $userAgent = 'Keitaro install.php curl/' . curl_version()['version'] . ' PHP/' . phpversion();
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            $content = curl_exec($ch);
            if ($error = curl_error($ch)) {
                return new GeneralResult(false, KLocaleService::t('step4.connection_error'));
            }
            curl_close($ch);
        } else {
            $f = fopen($url, 'r');
            $content = fread($f, 512000);
            if (!$f) {
                return new GeneralResult(false, KLocaleService::t('step4.connection_error'));
            }
        }
        return new GeneralResult(true, $content);
    }
    public function download($from, $to)
    {
        if (isset($this->stubbedRequests[$from])) {
            file_put_contents($to, $this->stubbedRequests[$from]);
            return new GeneralResult(true, "");
        }
        if (defined('PHPUNIT')) {
            throw new \Exception("Trying request '{$from}'. External requests forbidden in testing env. Use DownloadService::instance()->stub(from, result); ");
        }
        @set_time_limit(180);
        if (!file_exists($from)) {
            if (!ini_get('allow_url_fopen')) {
                if (function_exists('curl_init')) {
                    if ($this->_isCurlDisabled()) {
                        return new GeneralResult(false, KLocaleService::t('step4.curl_error'));
                    }
                    $ch = curl_init($from);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    $content = curl_exec($ch);
                    if ($error = curl_error($ch)) {
                        $msg = KLocaleService::t('step4.connection_error') . ' Curl error: ' . $error;
                        return new GeneralResult(false, $msg);
                    }
                    curl_close($ch);
                    if (!file_put_contents($to, $content)) {
                        return new GeneralResult(false, KLocaleService::t('step4.permission_error'));
                    }
                } else {
                    return new GeneralResult(false, KLocaleService::t('step4.tools_error'));
                }
            } else {
                $fileHandler = fopen($from, 'rb');
                if ($fileHandler === false) {
                    $msg = KLocaleService::t('step4.connection_error') . ' Cannot open url: ' . $http_response_header[0];
                    return new GeneralResult(false, $msg);
                }
                $result = file_put_contents($to, $fileHandler);
                if ($result === false) {
                    $error = error_get_last();
                    $msg = KLocaleService::t('step4.connection_error') . ' File_put_contents error: ' . isset($error) ? $error['message'] : 'Unknown error';
                    return new GeneralResult(false, $msg);
                }
            }
        }
        return new GeneralResult(true, "");
    }
    public function downloadAndValidate($url, $crcUrl, $archive, $file)
    {
        $localArchive = $archive;
        $directory = dirname($localArchive);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        if (defined('SKIP_DOWNLOAD')) {
            return new GeneralResult(false, "");
        }
        $result = $this->download($url, $localArchive);
        if (!$result->getStatus()) {
            return $result;
        }
        $this->download($crcUrl, $file . '.crc');
        if (!$result->getStatus()) {
            return $result;
        }
        ArchiveService::instance()->unzip($localArchive, $file);
        $isError = false;
        $errorMessage = '';
        $crc32 = file_get_contents($file . '.crc');
        if ((int) $crc32 === 0) {
            $isError = true;
            $errorMessage = ' ' . KLocaleService::t('step6.crc32_remote_empty');
        }
        $downloadedFileCrc = $this->_calculateCrc32($file);
        if ((int) $downloadedFileCrc === 0) {
            $isError = true;
            $errorMessage = ' ' . KLocaleService::t('step6.crc32_local_empty');
        }
        if ($crc32 != $downloadedFileCrc) {
            $isError = true;
            $errorMessage = '(' . $crc32 . '<>' . $downloadedFileCrc . ')';
        }
        if ($isError) {
            return new GeneralResult(false, KLocaleService::t('step6.crc32_failed') . $errorMessage);
        } else {
            return new GeneralResult(true, "");
        }
    }
    private function _calculateCrc32($file)
    {
        $fileString = file_get_contents($file);
        $crc = crc32($fileString);
        return abs($crc);
    }
    private function _isCurlDisabled()
    {
        $disabled = explode(',', ini_get('disable_functions'));
        if (in_array('curl_exec', $disabled)) {
            return true;
        }
        return false;
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class MysqlService extends AbstractService
{
    private $pdo = null;
    public function checkDatabase($host, $user, $pass, $db)
    {
        return $this->connect($host, $user, $pass, $db, ParameterService::instance()->getCreateDatabaseParam());
    }
    private function connect($host, $user, $pass, $db, $shouldCreate)
    {
        if (!$this->pdo instanceof \PDO) {
            $dsn = "mysql:host={$host};dbname=INFORMATION_SCHEMA;charset=utf8";
            $opt = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC);
            try {
                $this->pdo = new \PDO($dsn, $user, $pass, $opt);
            } catch (\PDOException $e) {
                return new GeneralResult(false, KLocaleService::t('step7.connection_error') . ': ' . $e->getMessage());
            }
        }
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $db . "'");
        if (!(bool) $stmt->fetchColumn()) {
            if ($shouldCreate) {
                try {
                    $result = $this->pdo->exec('CREATE DATABASE `' . $db . '`;');
                } catch (\PDOException $e) {
                    return new GeneralResult(false, KLocaleService::t('step7.cant_create') . ' "' . $db . '"');
                }
            } else {
                return new GeneralResult(false, KLocaleService::t('step7.db_error') . ' "' . $db . '"');
            }
        }
        $this->pdo->exec('USE `' . $db . '`;');
        $this->pdo->exec("set character_set_client=utf8");
        $this->pdo->exec("set character_set_connection=utf8");
        $this->pdo->exec("set character_set_results=utf8");
        $this->pdo->exec("set collation_connection=utf8_unicode_ci");
        return new GeneralResult(true, "");
    }
    public function connectFromIni()
    {
        $cnf = ConfigService::instance()->getIniConfig();
        return $this->connect($cnf['db']['server'], $cnf['db']['user'], $cnf['db']['password'], $cnf['db']['name'], false);
    }
    public function executeSql()
    {
        $isKeitaro7 = ConfigService::instance()->isKeitaro7();
        try {
            if (!$isKeitaro7) {
                $sqls = $this->_loadSql('application/data/schema.sql');
                if ($this->_checkHasKeitaro8Migrations()) {
                    $sqls = $this->_filterInsert($sqls);
                }
                if (ParameterService::instance()->isForceTokuDb()) {
                    $sqls = $this->replaceInnodbToTokuDB($sqls);
                }
            } else {
                $sqls = $this->_loadSql('application/data/tables.sql');
            }
            $this->_restore($sqls);
            $sqls = $this->_loadSql('application/data/data.sql');
            $this->_restore($sqls);
            return new GeneralResult(true, "");
        } catch (\PDOException $e) {
            return new GeneralResult(false, KLocaleService::t('step8.import_failure') . ":" . $e->getMessage());
        }
    }
    public function setStabilityChannel($stabilityChannel)
    {
        if ($stabilityChannel == ParameterService::UNSTABLE) {
            $sql = 'UPDATE keitaro_settings SET `value` = 1 WHERE `key` = \'is_beta_channel\'';
            $this->_restore([$sql]);
        }
    }
    private function _loadSql($file)
    {
        $handle = fopen($file, "rb");
        $data = '';
        while ($tmp = fread($handle, 4096)) {
            $strings = explode("\n", $tmp);
            $cur = 0;
            foreach ($strings as $string) {
                $cur++;
                if (substr($string, 0, 1) != '#') {
                    $data .= $string;
                }
            }
        }
        fclose($handle);
        return explode(";", $data);
    }
    private function _checkHasKeitaro8Migrations()
    {
        if (!$this->_tableExists('schema_migrations')) {
            return false;
        }
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM schema_migrations");
        $result = $stmt->fetchColumn();
        return (bool) (int) $result;
    }
    private function _tableExists($tableName)
    {
        $sql = 'SELECT 1 FROM ' . $tableName . ' LIMIT 1';
        try {
            $stmt = $this->pdo->query($sql);
            $stmt->fetchColumn();
        } catch (\PDOException $e) {
            return false;
        }
        return true;
    }
    private function _filterInsert($sqls)
    {
        $result = [];
        $insert = 'INSERT';
        foreach ($sqls as $sql) {
            if (substr($sql, 0, strlen($insert)) === $insert) {
                break;
            }
            $result[] = $sql;
        }
        return $result;
    }
    public function replaceInnodbToTokuDB($sqls)
    {
        $result = [];
        foreach ($sqls as $sql) {
            $sql = str_replace('ENGINE=InnoDB DEFAULT CHARSET=utf8', 'ENGINE=TokuDB DEFAULT CHARSET=utf8', $sql);
            $result[] = $sql;
        }
        return $result;
    }
    private function _restore($sqls)
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $status = true;
        foreach ($sqls as $sql) {
            $sql = str_replace("keitaro_", $cnf['db']['prefix'], $sql);
            if ($status === false) {
                break;
            }
            if (trim($sql) != '') {
                $status = $this->pdo->exec($sql);
            }
        }
        return $status;
    }
    public function setAdminPass($login, $pass)
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $password = md5($pass . $cnf['system']['salt']);
        $rules = serialize(array());
        $type = 'ADMIN';
        $sql = "INSERT IGNORE INTO " . $cnf['db']['prefix'] . "users (id, login, password, rules, `type`) VALUES\n                (1, '" . $login . "', '" . $password . "', '" . $rules . "', '" . $type . "')";
        $this->pdo->exec($sql);
        $sql = "UPDATE " . $cnf['db']['prefix'] . "users SET `login` = '" . $login . "', `password`='" . $password . "' WHERE id = 1";
        $this->pdo->exec($sql);
    }
    public function setCacheStorage($cache)
    {
        $this->setSetting('cache_storage', $cache);
    }
    public function setSetting($settingName, $value)
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $sql = "UPDATE " . $cnf['db']['prefix'] . "settings SET `value`='" . $value . "' WHERE `key`='" . $settingName . "'";
        $this->pdo->exec($sql);
        $sql = "INSERT IGNORE " . $cnf['db']['prefix'] . "settings (`key`, `value`) VALUES ('" . $settingName . "', '" . $value . "')";
        $this->pdo->exec($sql);
    }
    public function setDraftStorage($draft)
    {
        $this->setSetting('draft_data_storage', $draft);
    }
    public function setLanguage($language)
    {
        if (!ConfigService::instance()->isKeitaro7()) {
            $ids = $this->_getAllUserIds();
            foreach ($ids as $id) {
                $this->_setUserPreferencesValue($id, 'language', $language);
            }
        } else {
            $this->setSetting('language', $language);
        }
    }
    private function _getAllUserIds()
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $stmt = $this->pdo->query("SELECT id FROM " . $cnf['db']['prefix'] . "users");
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $row) {
            $result[] = $row;
        }
        return $result;
    }
    private function _setUserPreferencesValue($userId, $key, $value)
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $sql = "INSERT IGNORE INTO " . $cnf['db']['prefix'] . "user_preferences (`user_id`, `pref_name`, `pref_value`) \n            VALUES  ('" . $userId . "', '" . $key . "', '" . $value . "')";
        $this->pdo->exec($sql);
        $sql = "UPDATE " . $cnf['db']['prefix'] . "user_preferences SET `pref_value` = '" . $value . "' WHERE `user_id` = '" . $userId . "' AND `pref_name` = '" . $key . "'";
        $this->pdo->exec($sql);
    }
    public function setCurrency($currency)
    {
        $this->setSetting('currency', $currency);
    }
    public function setInstalledAt()
    {
        $this->setSetting('installed_at', ConfigService::instance()->getUTCTime());
    }
    public function setTimezone($timezone)
    {
        $id = $this->_getFirstAdminId();
        if ($id !== false) {
            $this->_setUserPreferencesValue($id, 'timezone', $timezone);
        }
    }
    private function _getFirstAdminId()
    {
        $cnf = ConfigService::instance()->getIniConfig();
        $stmt = $this->pdo->query("SELECT id FROM " . $cnf['db']['prefix'] . "users WHERE type = 'ADMIN' ORDER BY id DESC LIMIT 1");
        return $stmt->fetchColumn();
    }
    public function hasTokuDb()
    {
        try {
            $stmt = $this->pdo->query("SELECT Engine FROM `information_schema`.`ENGINES` WHERE Engine = 'TokuDB';");
            $row = $stmt->fetchColumn();
            return !empty($row);
        } catch (\PDOException $e) {
            return false;
        }
    }
    public function updateEngineToTokuDb()
    {
        try {
            $cnf = ConfigService::instance()->getIniConfig();
            $stmt = $this->pdo->query("SELECT TABLE_NAME \n            FROM INFORMATION_SCHEMA.TABLES\n            WHERE TABLE_SCHEMA = '" . $cnf['db']['name'] . "' \n                AND TABLE_NAME LIKE '" . $cnf['db']['prefix'] . "%'");
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $row) {
                $this->pdo->exec("ALTER TABLE `" . $row . "` ENGINE=TokuDB");
            }
        } catch (\PDOException $e) {
        }
    }
}
}

namespace Services {
class FileService extends AbstractService
{
    public function rmdir($path)
    {
        if (is_dir($path) and !is_link($path)) {
            foreach (scandir($path) as $value) {
                if ($value != "." && $value != "..") {
                    $value = $path . "/" . $value;
                    if (is_dir($value)) {
                        $this->rmdir($value);
                    } elseif (is_file($value)) {
                        unlink($value);
                    }
                }
            }
            return rmdir($path);
        } else {
            return false;
        }
    }
    public function chmod($path, $attr = 0777)
    {
        $d = @opendir($path);
        if (!@is_dir($d) || is_link($d)) {
            return;
        }
        while (($file = readdir($d)) !== false) {
            if ($file != "." && $file != ".." && $file != $_SERVER['PHP_SELF']) {
                $typepath = $path . "/" . $file;
                if (filetype($typepath) == 'dir') {
                    $this->chmod($typepath, $attr);
                }
                @chmod($typepath, $attr);
            }
        }
    }
    public function chown($path, $uid, $gid)
    {
        $d = @opendir($path);
        if (!@is_dir($d) || is_link($d)) {
            return;
        }
        while (($file = readdir($d)) !== false) {
            if ($file != "." && $file != ".." && $file != $_SERVER['PHP_SELF']) {
                $typepath = $path . "/" . $file;
                if (filetype($typepath) == 'dir') {
                    $this->chown($typepath, $uid, $gid);
                }
                @chown($typepath, $uid);
                @chgrp($typepath, $gid);
            }
        }
    }
    public function dirmv($src, $dest)
    {
        if (!is_dir($src)) {
            return false;
        }
        if (!is_dir($dest)) {
            if (!mkdir($dest)) {
                return false;
            }
        }
        $i = new \DirectoryIterator($src);
        foreach ($i as $f) {
            if ($f->isFile()) {
                rename($f->getRealPath(), "{$dest}/" . $f->getFilename());
                @unlink($f->getRealPath());
            } else {
                if (!$f->isDot() && $f->isDir()) {
                    $this->dirmv($f->getRealPath(), "{$dest}/{$f}");
                    @unlink($f->getRealPath());
                }
            }
        }
        @unlink($src);
    }
}
}

namespace Services {
class RedirectService extends AbstractService
{
    public function toStep($step)
    {
        header('LOCATION:?step=' . $step);
    }
}
}

namespace Services {
use ResultSets\GeneralResult;
class ArchiveService extends AbstractService
{
    public function gzopen($filename, $mode, $use_include_path = 0)
    {
        if (!function_exists('gzopen') && function_exists('gzopen64')) {
            return gzopen64($filename, $mode, $use_include_path);
        } else {
            return gzopen($filename, $mode, $use_include_path);
        }
    }
    public function unpack($localPackage)
    {
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zip->open($localPackage);
            $zip->extractTo('./');
            $zip->close();
        } elseif (class_exists('PharData')) {
            @mkdir('www', 0777);
            try {
                $archive = new \PharData($localPackage);
                $archive->extractTo('./', null, true);
            } catch (\Exception $e) {
                return new GeneralResult(false, $e->getMessage());
            }
        } else {
            $disabled = ini_get('disable_functions');
            if (!strstr($disabled, 'system')) {
                system("tar -xzf " . $localPackage);
            } elseif (!strstr($disabled, 'exec')) {
                exec("tar -xzf " . $localPackage);
            }
        }
        if (!file_exists('www/version.php')) {
            return new GeneralResult(false, "Unable to unpack. Please install php_zip.");
        } else {
            return new GeneralResult(true, "");
        }
    }
    public function unzip($archive, $file)
    {
        $sfp = $this->gzopen($archive, "rb");
        $fp = fopen($file, "w");
        while ($string = gzread($sfp, 4096)) {
            fwrite($fp, $string, strlen($string));
        }
        gzclose($sfp);
        fclose($fp);
        unlink($archive);
    }
}
}

namespace Views {
use ResultSets\Step1ResultSet;
use ResultSets\Step3ResultSet;
use Services\KLocaleService;
use Services\ConfigService;
use Services\ParameterService;
use ResultSets\SingleElementResultSet;
use ResultSets\ElementArrayResultSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class ConsoleView implements ViewInterface
{
    private $_input;
    private $_output;
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;
    }
    private function _step1(Step1ResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step1.title'));
        foreach ($data as $key => $value) {
            $row = $data->getMemberRow($key);
            $header = strip_tags($row[Step1ResultSet::HEADER_COL]);
            $text = $row[Step1ResultSet::STATUS_COL] ? $this->_wrapSuccess(strip_tags($row[Step1ResultSet::TEXT_COL])) : $this->_wrapError(strip_tags($row[Step1ResultSet::TEXT_COL]));
            $status = $row[Step1ResultSet::STATUS_COL] ? "OK" : "FAIL";
            $this->_output->writeln($header . ': ' . $text . ' (' . $status . ')');
        }
        if ($data->getStatus()) {
            $this->_writeSuccess(KLocaleService::t('step1.success'));
            if (ParameterService::instance()->isVerifyMode()) {
                exit;
            }
            return 2;
        } else {
            $this->_writeError(KLocaleService::t('step1.failure'));
            exit(1);
        }
    }
    private function _step2(SingleElementResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step2.title'));
        if ($data->getStatus()) {
            return 3;
        } else {
            $this->_writeError($data->getErrorText());
            $this->_writeError(KLocaleService::t('step2.check'));
            exit(1);
        }
    }
    private function _step3(Step3ResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step3.title'));
        if (!$data->getStatus()) {
            $this->_writeError(KLocaleService::t('step3.delete_www'));
            exit(1);
        } else {
            $this->_writeError(KLocaleService::t('step3.warning'));
            return 4;
        }
    }
    private function _step4(SingleElementResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step4.title'));
        if ($data->getStatus()) {
            $this->_writeSuccess(KLocaleService::t('step4.success'));
            return '4_2';
        } else {
            $this->_writeError($data->getErrorText());
            exit(1);
        }
    }
    private function _step4_2(SingleElementResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step4.title'));
        if ($data->getStatus()) {
            $this->_writeSuccess(KLocaleService::t('step4.unpacked'));
            return 5;
        } else {
            $this->_writeError($data->getErrorText());
            exit(1);
        }
    }
    private function _step5($data)
    {
        $this->_writeHeader(KLocaleService::t('step5.title'));
        return 6;
    }
    private function _step6(ElementArrayResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step6.title'));
        if ($data->getStatus()) {
            $this->_writeInfo(KLocaleService::t('step6.success'));
            return 7;
        } else {
            foreach ($data->getErrorTexts() as $text) {
                $this->_writeError($text);
            }
            exit(1);
        }
    }
    private function _step7(SingleElementResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step7.title'));
        if ($data->getStatus()) {
            die("Error");
        } else {
            $this->_writeError($data->getErrorText());
            exit(1);
        }
    }
    private function _step8(SingleElementResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step8.title'));
        if ($data->getStatus()) {
            $this->_writeSuccess(KLocaleService::t('step8.import_success'));
            return 9;
        } else {
            $this->_writeError($data->getErrorText());
            exit(1);
        }
    }
    private function _step9(ElementArrayResultSet $data)
    {
        $this->_writeHeader(KLocaleService::t('step9.title'));
        if ($data->getStatus()) {
            die("Wrong step 9");
        } else {
            foreach ($data->getErrorTexts() as $text) {
                $this->_writeError($text);
            }
            exit(1);
        }
    }
    private function _step10($data)
    {
        $this->_writeHeader(KLocaleService::t('step10.title'));
        $content = KLocaleService::t('step10.instruction');
        $content = str_replace('@document_root@', ConfigService::instance()->getDocumentRoot(), $content);
        $content = str_replace('@path@', '/PATH', $content);
        $content = str_replace('@host@', 'HOST', $content);
        $this->_writeInfo($content);
        return 11;
    }
    private function _step11($data)
    {
        $this->_writeHeader(KLocaleService::t('step11.title'));
        $this->_writeInfo(KLocaleService::t('step11.installation_complete'));
        return 12;
    }
    private function _step12($data)
    {
        touch('var/install.lock');
        exit;
    }
    private function _wrapSuccess($text)
    {
        return '<info>' . $text . '</info>';
    }
    private function _wrapError($text)
    {
        return '<error>' . $text . '</error>';
    }
    private function _writeError($text)
    {
        $this->_output->writeln($this->_wrapError(strip_tags($text)));
    }
    private function _writeSuccess($text)
    {
        $this->_output->writeln($this->_wrapSuccess(strip_tags($text)));
    }
    private function _writeInfo($text)
    {
        $this->_output->writeln(strip_tags($text));
    }
    private function _writeHeader($text)
    {
        $this->_output->writeln('<comment>' . strip_tags($text) . '</comment>');
    }
    public function renderStepPage($step, $data)
    {
        $action = '_step' . $step;
        return $this->{$action}($data);
    }
    public function renderLockedPage()
    {
        $this->_writeHeader(KLocaleService::t('installation_locked'));
        $this->_writeError(KLocaleService::t('remove_lock'));
    }
}
}

namespace Symfony\Component\Console\Command {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class Command
{
    protected static $defaultName;
    private $application;
    private $name;
    private $processTitle;
    private $aliases = array();
    private $definition;
    private $hidden = false;
    private $help;
    private $description;
    private $ignoreValidationErrors = false;
    private $applicationDefinitionMerged = false;
    private $applicationDefinitionMergedWithArgs = false;
    private $code;
    private $synopsis = array();
    private $usages = array();
    private $helperSet;
    public static function getDefaultName()
    {
        $class = \get_called_class();
        $r = new \ReflectionProperty($class, 'defaultName');
        return $class === $r->class ? static::$defaultName : null;
    }
    public function __construct($name = null)
    {
        $this->definition = new InputDefinition();
        if (null !== $name || null !== ($name = static::getDefaultName())) {
            $this->setName($name);
        }
        $this->configure();
    }
    public function ignoreValidationErrors()
    {
        $this->ignoreValidationErrors = true;
    }
    public function setApplication(Application $application = null)
    {
        $this->application = $application;
        if ($application) {
            $this->setHelperSet($application->getHelperSet());
        } else {
            $this->helperSet = null;
        }
    }
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet()
    {
        return $this->helperSet;
    }
    public function getApplication()
    {
        return $this->application;
    }
    public function isEnabled()
    {
        return true;
    }
    protected function configure()
    {
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new LogicException('You must override the execute() method in the concrete command class.');
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->getSynopsis(true);
        $this->getSynopsis(false);
        $this->mergeApplicationDefinition();
        try {
            $input->bind($this->definition);
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }
        $this->initialize($input, $output);
        if (null !== $this->processTitle) {
            if (\function_exists('cli_set_process_title')) {
                if (!@cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    } else {
                        cli_set_process_title($this->processTitle);
                    }
                }
            } elseif (\function_exists('setproctitle')) {
                setproctitle($this->processTitle);
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }
        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }
        $input->validate();
        if ($this->code) {
            $statusCode = \call_user_func($this->code, $input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
        }
        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                if (\PHP_VERSION_ID < 70000) {
                    $code = @\Closure::bind($code, $this);
                } else {
                    $code = \Closure::bind($code, $this);
                }
            }
        }
        $this->code = $code;
        return $this;
    }
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        if (null === $this->application || true === $this->applicationDefinitionMerged && ($this->applicationDefinitionMergedWithArgs || !$mergeArgs)) {
            return;
        }
        $this->definition->addOptions($this->application->getDefinition()->getOptions());
        $this->applicationDefinitionMerged = true;
        if ($mergeArgs) {
            $currentArguments = $this->definition->getArguments();
            $this->definition->setArguments($this->application->getDefinition()->getArguments());
            $this->definition->addArguments($currentArguments);
            $this->applicationDefinitionMergedWithArgs = true;
        }
    }
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
        $this->applicationDefinitionMerged = false;
        return $this;
    }
    public function getDefinition()
    {
        return $this->definition;
    }
    public function getNativeDefinition()
    {
        return $this->getDefinition();
    }
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));
        return $this;
    }
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));
        return $this;
    }
    public function setName($name)
    {
        $this->validateName($name);
        $this->name = $name;
        return $this;
    }
    public function setProcessTitle($title)
    {
        $this->processTitle = $title;
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setHidden($hidden)
    {
        $this->hidden = (bool) $hidden;
        return $this;
    }
    public function isHidden()
    {
        return $this->hidden;
    }
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setHelp($help)
    {
        $this->help = $help;
        return $this;
    }
    public function getHelp()
    {
        return $this->help;
    }
    public function getProcessedHelp()
    {
        $name = $this->name;
        $isSingleCommand = $this->application && $this->application->isSingleCommand();
        $placeholders = array('%command.name%', '%command.full_name%');
        $replacements = array($name, $isSingleCommand ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'] . ' ' . $name);
        return str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }
    public function setAliases($aliases)
    {
        if (!\is_array($aliases) && !$aliases instanceof \Traversable) {
            throw new InvalidArgumentException('$aliases must be an array or an instance of \\Traversable');
        }
        foreach ($aliases as $alias) {
            $this->validateName($alias);
        }
        $this->aliases = $aliases;
        return $this;
    }
    public function getAliases()
    {
        return $this->aliases;
    }
    public function getSynopsis($short = false)
    {
        $key = $short ? 'short' : 'long';
        if (!isset($this->synopsis[$key])) {
            $this->synopsis[$key] = trim(sprintf('%s %s', $this->name, $this->definition->getSynopsis($short)));
        }
        return $this->synopsis[$key];
    }
    public function addUsage($usage)
    {
        if (0 !== strpos($usage, $this->name)) {
            $usage = sprintf('%s %s', $this->name, $usage);
        }
        $this->usages[] = $usage;
        return $this;
    }
    public function getUsages()
    {
        return $this->usages;
    }
    public function getHelper($name)
    {
        if (null === $this->helperSet) {
            throw new LogicException(sprintf('Cannot retrieve helper "%s" because there is no HelperSet defined. Did you forget to add your command to the application or to set the application on the command using the setApplication() method? You can also set the HelperSet directly using the setHelperSet() method.', $name));
        }
        return $this->helperSet->get($name);
    }
    private function validateName($name)
    {
        if (!preg_match('/^[^\\:]++(\\:[^\\:]++)*$/', $name)) {
            throw new InvalidArgumentException(sprintf('Command name "%s" is invalid.', $name));
        }
    }
}
}

namespace Commands {
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Views\ConsoleView;
use Controllers\InstallController;
use Models\StepModel;
use Services\KLocaleService;
use Services\ParameterService;
class InstallCommand extends Command
{
    public $autoDispatch = true;
    protected function configure()
    {
        $this->setDescription("Install Keitaro")->setName('install')->addOption('domain', null, InputOption::VALUE_REQUIRED, 'Domain install to (optional)')->addOption('ip', null, InputOption::VALUE_REQUIRED, 'License ip (optional)')->addOption('key', null, InputOption::VALUE_REQUIRED, 'License key')->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'Database host (optional)')->addOption('db-prefix', null, InputOption::VALUE_REQUIRED, 'Database tables prefix (optional)')->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'Database username')->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'Database name')->addOption('db-password', null, InputOption::VALUE_REQUIRED, 'Database password')->addOption('admin-login', null, InputOption::VALUE_REQUIRED, 'Administrator login')->addOption('admin-password', null, InputOption::VALUE_REQUIRED, 'Administrator password')->addOption('extra-url', null, InputOption::VALUE_REQUIRED, 'Extra url for other TDS (optional)')->addOption('draft-storage', null, InputOption::VALUE_REQUIRED, 'Storage for draft data (optional)')->addOption('cache-storage', null, InputOption::VALUE_REQUIRED, 'Storage for cache data (optional)')->addOption('language', null, InputOption::VALUE_REQUIRED, 'language ru|en (default en)')->addOption('currency', null, InputOption::VALUE_REQUIRED, 'currency RUB|USD|EUR|GBP|UAH (default based on language)')->addOption('kversion', null, InputOption::VALUE_REQUIRED, 'version 8|9 (default 9)')->addOption('timezone', null, InputOption::VALUE_REQUIRED, 'timezone')->addOption('force-tokudb', null, InputOption::VALUE_NONE, 'force tokudb')->addOption('custom-package', null, InputOption::VALUE_REQUIRED, 'provide custom package url')->addOption('verify', null, InputOption::VALUE_NONE, 'just verify installer')->addOption('redis-uri', null, InputOption::VALUE_REQUIRED, 'Redis URI');
    }
    public function dispatch(InputInterface $input, OutputInterface $output)
    {
        $model = new StepModel(true);
        $view = new ConsoleView($input, $output);
        $controller = new InstallController($view, $model);
        $controller->dispatchChain();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getOption('domain');
        $ip = $input->getOption('ip');
        $key = $input->getOption('key');
        $dbHost = $input->getOption('db-host');
        $dbPrefix = $input->getOption('db-prefix');
        $dbUser = $input->getOption('db-user');
        $dbName = $input->getOption('db-name');
        $dbPassword = $input->getOption('db-password');
        $adminLogin = $input->getOption('admin-login');
        $adminPassword = $input->getOption('admin-password');
        $redisUri = $input->getOption('redis-uri');
        $extraUrl = $input->getOption('extra-url');
        $draft = $input->getOption('draft-storage');
        $cache = $input->getOption('cache-storage');
        $language = $input->getOption('language');
        $currency = $input->getOption('currency');
        $version = $input->getOption('kversion');
        $timezone = $input->getOption('timezone');
        $forceTokuDb = $input->getOption('force-tokudb');
        $customPackage = $input->getOption('custom-package');
        $verify = $input->getOption('verify');
        if (!$verify) {
            if (empty($key)) {
                $output->writeln("Mandatory params missing: key");
                return;
            }
            if (empty($dbName)) {
                $output->writeln("Mandatory params missing: db-name");
                return;
            }
            if (empty($dbPassword)) {
                $output->writeln("Mandatory params missing: db-password");
                return;
            }
            if (empty($adminLogin)) {
                $output->writeln("Mandatory params missing: admin-login");
                return;
            }
            if (empty($adminPassword)) {
                $output->writeln("Mandatory params missing: admin-password");
                return;
            }
        }
        if (!$ip && $domain) {
            $ip = gethostbyname($domain);
        }
        if (!$dbHost) {
            $dbHost = 'localhost';
        }
        if (!$dbPrefix) {
            $dbPrefix = 'keitaro_';
        }
        if (!$redisUri) {
            $redisUri = '127.0.0.1:6379/1';
        }
        if (!$extraUrl) {
            $extraUrl = 'https://google.com';
        }
        if (!$language) {
            $language = 'en';
        }
        if (!$currency) {
            if ($language == 'en') {
                $currency = 'USD';
            } else {
                $currency = 'RUB';
            }
        } else {
            if (!in_array($currency, array('RUB', 'USD', 'EUR', 'GBP', 'UAH'))) {
                $output->writeln("Currency should be RUB|USD|EUR|GBP|UAH");
                return;
            }
        }
        if (!$version) {
            $version = 9;
        }
        KLocaleService::setPreferredLanguage($language);
        $ps = ParameterService::instance();
        $ps->savePersistent(ParameterService::DOMAIN, $domain);
        $ps->savePersistent(ParameterService::IP, $ip);
        $ps->savePersistent(ParameterService::KEY, $key);
        $ps->savePersistent(ParameterService::DB_HOST, $dbHost);
        $ps->savePersistent(ParameterService::DB_PREFIX, $dbPrefix);
        $ps->savePersistent(ParameterService::DB_USER, $dbUser);
        $ps->savePersistent(ParameterService::DB_NAME, $dbName);
        $ps->savePersistent(ParameterService::DB_PASS, $dbPassword);
        $ps->savePersistent(ParameterService::REDIS_URI, $redisUri);
        $ps->savePersistent(ParameterService::ADMIN_LOGIN, $adminLogin);
        $ps->savePersistent(ParameterService::ADMIN_PASS, $adminPassword);
        $ps->savePersistent(ParameterService::ADMIN_PASS_CONFIRM, $adminPassword);
        $ps->savePersistent(ParameterService::EXTRA_URL, $extraUrl);
        $ps->savePersistent(ParameterService::DRAFT_STORAGE, $draft);
        $ps->savePersistent(ParameterService::CACHE_STORAGE, $cache);
        $ps->savePersistent(ParameterService::LANGUAGE, $language);
        $ps->savePersistent(ParameterService::CURRENCY, $currency);
        $ps->savePersistent(ParameterService::VERSION, $version);
        $ps->savePersistent(ParameterService::TIMEZONE, $timezone);
        $ps->savePersistent(ParameterService::FORCE_TOKUDB, $forceTokuDb !== false);
        $ps->savePersistent(ParameterService::CUSTOM_PACKAGE, $customPackage);
        $ps->savePersistent(ParameterService::VERIFY, $verify);
        if ($this->autoDispatch) {
            $this->dispatch($input, $output);
        }
    }
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
class InputDefinition
{
    private $arguments;
    private $requiredCount;
    private $hasAnArrayArgument = false;
    private $hasOptional;
    private $options;
    private $shortcuts;
    public function __construct(array $definition = array())
    {
        $this->setDefinition($definition);
    }
    public function setDefinition(array $definition)
    {
        $arguments = array();
        $options = array();
        foreach ($definition as $item) {
            if ($item instanceof InputOption) {
                $options[] = $item;
            } else {
                $arguments[] = $item;
            }
        }
        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    public function setArguments($arguments = array())
    {
        $this->arguments = array();
        $this->requiredCount = 0;
        $this->hasOptional = false;
        $this->hasAnArrayArgument = false;
        $this->addArguments($arguments);
    }
    public function addArguments($arguments = array())
    {
        if (null !== $arguments) {
            foreach ($arguments as $argument) {
                $this->addArgument($argument);
            }
        }
    }
    public function addArgument(InputArgument $argument)
    {
        if (isset($this->arguments[$argument->getName()])) {
            throw new LogicException(sprintf('An argument with name "%s" already exists.', $argument->getName()));
        }
        if ($this->hasAnArrayArgument) {
            throw new LogicException('Cannot add an argument after an array argument.');
        }
        if ($argument->isRequired() && $this->hasOptional) {
            throw new LogicException('Cannot add a required argument after an optional one.');
        }
        if ($argument->isArray()) {
            $this->hasAnArrayArgument = true;
        }
        if ($argument->isRequired()) {
            ++$this->requiredCount;
        } else {
            $this->hasOptional = true;
        }
        $this->arguments[$argument->getName()] = $argument;
    }
    public function getArgument($name)
    {
        if (!$this->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;
        return $arguments[$name];
    }
    public function hasArgument($name)
    {
        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;
        return isset($arguments[$name]);
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getArgumentCount()
    {
        return $this->hasAnArrayArgument ? PHP_INT_MAX : \count($this->arguments);
    }
    public function getArgumentRequiredCount()
    {
        return $this->requiredCount;
    }
    public function getArgumentDefaults()
    {
        $values = array();
        foreach ($this->arguments as $argument) {
            $values[$argument->getName()] = $argument->getDefault();
        }
        return $values;
    }
    public function setOptions($options = array())
    {
        $this->options = array();
        $this->shortcuts = array();
        $this->addOptions($options);
    }
    public function addOptions($options = array())
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }
    public function addOption(InputOption $option)
    {
        if (isset($this->options[$option->getName()]) && !$option->equals($this->options[$option->getName()])) {
            throw new LogicException(sprintf('An option named "%s" already exists.', $option->getName()));
        }
        if ($option->getShortcut()) {
            foreach (explode('|', $option->getShortcut()) as $shortcut) {
                if (isset($this->shortcuts[$shortcut]) && !$option->equals($this->options[$this->shortcuts[$shortcut]])) {
                    throw new LogicException(sprintf('An option with shortcut "%s" already exists.', $shortcut));
                }
            }
        }
        $this->options[$option->getName()] = $option;
        if ($option->getShortcut()) {
            foreach (explode('|', $option->getShortcut()) as $shortcut) {
                $this->shortcuts[$shortcut] = $option->getName();
            }
        }
    }
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "--%s" option does not exist.', $name));
        }
        return $this->options[$name];
    }
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function hasShortcut($name)
    {
        return isset($this->shortcuts[$name]);
    }
    public function getOptionForShortcut($shortcut)
    {
        return $this->getOption($this->shortcutToName($shortcut));
    }
    public function getOptionDefaults()
    {
        $values = array();
        foreach ($this->options as $option) {
            $values[$option->getName()] = $option->getDefault();
        }
        return $values;
    }
    private function shortcutToName($shortcut)
    {
        if (!isset($this->shortcuts[$shortcut])) {
            throw new InvalidArgumentException(sprintf('The "-%s" option does not exist.', $shortcut));
        }
        return $this->shortcuts[$shortcut];
    }
    public function getSynopsis($short = false)
    {
        $elements = array();
        if ($short && $this->getOptions()) {
            $elements[] = '[options]';
        } elseif (!$short) {
            foreach ($this->getOptions() as $option) {
                $value = '';
                if ($option->acceptValue()) {
                    $value = sprintf(' %s%s%s', $option->isValueOptional() ? '[' : '', strtoupper($option->getName()), $option->isValueOptional() ? ']' : '');
                }
                $shortcut = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
                $elements[] = sprintf('[%s--%s%s]', $shortcut, $option->getName(), $value);
            }
        }
        if (\count($elements) && $this->getArguments()) {
            $elements[] = '[--]';
        }
        foreach ($this->getArguments() as $argument) {
            $element = '<' . $argument->getName() . '>';
            if (!$argument->isRequired()) {
                $element = '[' . $element . ']';
            } elseif ($argument->isArray()) {
                $element .= ' (' . $element . ')';
            }
            if ($argument->isArray()) {
                $element .= '...';
            }
            $elements[] = $element;
        }
        return implode(' ', $elements);
    }
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
class InputOption
{
    const VALUE_NONE = 1;
    const VALUE_REQUIRED = 2;
    const VALUE_OPTIONAL = 4;
    const VALUE_IS_ARRAY = 8;
    private $name;
    private $shortcut;
    private $mode;
    private $default;
    private $description;
    public function __construct($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        if (0 === strpos($name, '--')) {
            $name = substr($name, 2);
        }
        if (empty($name)) {
            throw new InvalidArgumentException('An option name cannot be empty.');
        }
        if (empty($shortcut)) {
            $shortcut = null;
        }
        if (null !== $shortcut) {
            if (\is_array($shortcut)) {
                $shortcut = implode('|', $shortcut);
            }
            $shortcuts = preg_split('{(\\|)-?}', ltrim($shortcut, '-'));
            $shortcuts = array_filter($shortcuts);
            $shortcut = implode('|', $shortcuts);
            if (empty($shortcut)) {
                throw new InvalidArgumentException('An option shortcut cannot be empty.');
            }
        }
        if (null === $mode) {
            $mode = self::VALUE_NONE;
        } elseif (!\is_int($mode) || $mode > 15 || $mode < 1) {
            throw new InvalidArgumentException(sprintf('Option mode "%s" is not valid.', $mode));
        }
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
        if ($this->isArray() && !$this->acceptValue()) {
            throw new InvalidArgumentException('Impossible to have an option mode VALUE_IS_ARRAY if the option does not accept a value.');
        }
        $this->setDefault($default);
    }
    public function getShortcut()
    {
        return $this->shortcut;
    }
    public function getName()
    {
        return $this->name;
    }
    public function acceptValue()
    {
        return $this->isValueRequired() || $this->isValueOptional();
    }
    public function isValueRequired()
    {
        return self::VALUE_REQUIRED === (self::VALUE_REQUIRED & $this->mode);
    }
    public function isValueOptional()
    {
        return self::VALUE_OPTIONAL === (self::VALUE_OPTIONAL & $this->mode);
    }
    public function isArray()
    {
        return self::VALUE_IS_ARRAY === (self::VALUE_IS_ARRAY & $this->mode);
    }
    public function setDefault($default = null)
    {
        if (self::VALUE_NONE === (self::VALUE_NONE & $this->mode) && null !== $default) {
            throw new LogicException('Cannot set a default value when using InputOption::VALUE_NONE mode.');
        }
        if ($this->isArray()) {
            if (null === $default) {
                $default = array();
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array option must be an array.');
            }
        }
        $this->default = $this->acceptValue() ? $default : false;
    }
    public function getDefault()
    {
        return $this->default;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function equals(self $option)
    {
        return $option->getName() === $this->getName() && $option->getShortcut() === $this->getShortcut() && $option->getDefault() === $this->getDefault() && $option->isArray() === $this->isArray() && $option->isValueRequired() === $this->isValueRequired() && $option->isValueOptional() === $this->isValueOptional();
    }
}
}

namespace Symfony\Component\Console {
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
class Application
{
    private $commands = array();
    private $wantHelps = false;
    private $runningCommand;
    private $name;
    private $version;
    private $commandLoader;
    private $catchExceptions = true;
    private $autoExit = true;
    private $definition;
    private $helperSet;
    private $dispatcher;
    private $terminal;
    private $defaultCommand;
    private $singleCommand = false;
    private $initialized;
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->name = $name;
        $this->version = $version;
        $this->terminal = new Terminal();
        $this->defaultCommand = 'list';
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    public function setCommandLoader(CommandLoaderInterface $commandLoader)
    {
        $this->commandLoader = $commandLoader;
    }
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        putenv('LINES=' . $this->terminal->getHeight());
        putenv('COLUMNS=' . $this->terminal->getWidth());
        if (null === $input) {
            $input = new ArgvInput();
        }
        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $renderException = function ($e) use($output) {
            if (!$e instanceof \Exception) {
                $e = class_exists(FatalThrowableError::class) ? new FatalThrowableError($e) : new \ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine());
            }
            if ($output instanceof ConsoleOutputInterface) {
                $this->renderException($e, $output->getErrorOutput());
            } else {
                $this->renderException($e, $output);
            }
        };
        if ($phpHandler = set_exception_handler($renderException)) {
            restore_exception_handler();
            if (!\is_array($phpHandler) || !$phpHandler[0] instanceof ErrorHandler) {
                $debugHandler = true;
            } elseif ($debugHandler = $phpHandler[0]->setExceptionHandler($renderException)) {
                $phpHandler[0]->setExceptionHandler($debugHandler);
            }
        }
        if (null !== $this->dispatcher && $this->dispatcher->hasListeners(ConsoleEvents::EXCEPTION)) {
            @trigger_error(sprintf('The "ConsoleEvents::EXCEPTION" event is deprecated since Symfony 3.3 and will be removed in 4.0. Listen to the "ConsoleEvents::ERROR" event instead.'), E_USER_DEPRECATED);
        }
        $this->configureIO($input, $output);
        try {
            $exitCode = $this->doRun($input, $output);
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }
            $renderException($e);
            $exitCode = $e->getCode();
            if (is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if (0 === $exitCode) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        } finally {
            if (!$phpHandler) {
                if (set_exception_handler($renderException) === $renderException) {
                    restore_exception_handler();
                }
                restore_exception_handler();
            } elseif (!$debugHandler) {
                $finalHandler = $phpHandler[0]->setExceptionHandler(null);
                if ($finalHandler !== $renderException) {
                    $phpHandler[0]->setExceptionHandler($finalHandler);
                }
            }
        }
        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }
            exit($exitCode);
        }
        return $exitCode;
    }
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--version', '-V'), true)) {
            $output->writeln($this->getLongVersion());
            return 0;
        }
        $name = $this->getCommandName($input);
        if (true === $input->hasParameterOption(array('--help', '-h'), true)) {
            if (!$name) {
                $name = 'help';
                $input = new ArrayInput(array('command_name' => $this->defaultCommand));
            } else {
                $this->wantHelps = true;
            }
        }
        if (!$name) {
            $name = $this->defaultCommand;
            $definition = $this->getDefinition();
            $definition->setArguments(array_merge($definition->getArguments(), array('command' => new InputArgument('command', InputArgument::OPTIONAL, $definition->getArgument('command')->getDescription(), $name))));
        }
        try {
            $e = $this->runningCommand = null;
            $command = $this->find($name);
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }
        if (null !== $e) {
            if (null !== $this->dispatcher) {
                $event = new ConsoleErrorEvent($input, $output, $e);
                $this->dispatcher->dispatch(ConsoleEvents::ERROR, $event);
                $e = $event->getError();
                if (0 === $event->getExitCode()) {
                    return 0;
                }
            }
            throw $e;
        }
        $this->runningCommand = $command;
        $exitCode = $this->doRunCommand($command, $input, $output);
        $this->runningCommand = null;
        return $exitCode;
    }
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet()
    {
        if (!$this->helperSet) {
            $this->helperSet = $this->getDefaultHelperSet();
        }
        return $this->helperSet;
    }
    public function setDefinition(InputDefinition $definition)
    {
        $this->definition = $definition;
    }
    public function getDefinition()
    {
        if (!$this->definition) {
            $this->definition = $this->getDefaultInputDefinition();
        }
        if ($this->singleCommand) {
            $inputDefinition = $this->definition;
            $inputDefinition->setArguments();
            return $inputDefinition;
        }
        return $this->definition;
    }
    public function getHelp()
    {
        return $this->getLongVersion();
    }
    public function areExceptionsCaught()
    {
        return $this->catchExceptions;
    }
    public function setCatchExceptions($boolean)
    {
        $this->catchExceptions = (bool) $boolean;
    }
    public function isAutoExitEnabled()
    {
        return $this->autoExit;
    }
    public function setAutoExit($boolean)
    {
        $this->autoExit = (bool) $boolean;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function setVersion($version)
    {
        $this->version = $version;
    }
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <info>%s</info>', $this->getName(), $this->getVersion());
            }
            return $this->getName();
        }
        return 'Console Tool';
    }
    public function register($name)
    {
        return $this->add(new Command($name));
    }
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }
    public function add(Command $command)
    {
        $this->init();
        $command->setApplication($this);
        if (!$command->isEnabled()) {
            $command->setApplication(null);
            return;
        }
        if (null === $command->getDefinition()) {
            throw new LogicException(sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', \get_class($command)));
        }
        if (!$command->getName()) {
            throw new LogicException(sprintf('The command defined in "%s" cannot have an empty name.', \get_class($command)));
        }
        $this->commands[$command->getName()] = $command;
        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }
        return $command;
    }
    public function get($name)
    {
        $this->init();
        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $name));
        }
        $command = $this->commands[$name];
        if ($this->wantHelps) {
            $this->wantHelps = false;
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);
            return $helpCommand;
        }
        return $command;
    }
    public function has($name)
    {
        $this->init();
        return isset($this->commands[$name]) || $this->commandLoader && $this->commandLoader->has($name) && $this->add($this->commandLoader->get($name));
    }
    public function getNamespaces()
    {
        $namespaces = array();
        foreach ($this->all() as $command) {
            $namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));
            foreach ($command->getAliases() as $alias) {
                $namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
            }
        }
        return array_values(array_unique(array_filter($namespaces)));
    }
    public function findNamespace($namespace)
    {
        $allNamespaces = $this->getNamespaces();
        $expr = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $namespace);
        $namespaces = preg_grep('{^' . $expr . '}', $allNamespaces);
        if (empty($namespaces)) {
            $message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);
            if ($alternatives = $this->findAlternatives($namespace, $allNamespaces)) {
                if (1 == \count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= implode("\n    ", $alternatives);
            }
            throw new CommandNotFoundException($message, $alternatives);
        }
        $exact = \in_array($namespace, $namespaces, true);
        if (\count($namespaces) > 1 && !$exact) {
            throw new CommandNotFoundException(sprintf("The namespace \"%s\" is ambiguous.\nDid you mean one of these?\n%s", $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))), array_values($namespaces));
        }
        return $exact ? $namespace : reset($namespaces);
    }
    public function find($name)
    {
        $this->init();
        $aliases = array();
        $allCommands = $this->commandLoader ? array_merge($this->commandLoader->getNames(), array_keys($this->commands)) : array_keys($this->commands);
        $expr = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $name);
        $commands = preg_grep('{^' . $expr . '}', $allCommands);
        if (empty($commands)) {
            $commands = preg_grep('{^' . $expr . '}i', $allCommands);
        }
        if (empty($commands) || \count(preg_grep('{^' . $expr . '$}i', $commands)) < 1) {
            if (false !== ($pos = strrpos($name, ':'))) {
                $this->findNamespace(substr($name, 0, $pos));
            }
            $message = sprintf('Command "%s" is not defined.', $name);
            if ($alternatives = $this->findAlternatives($name, $allCommands)) {
                if (1 == \count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= implode("\n    ", $alternatives);
            }
            throw new CommandNotFoundException($message, $alternatives);
        }
        if (\count($commands) > 1) {
            $commandList = $this->commandLoader ? array_merge(array_flip($this->commandLoader->getNames()), $this->commands) : $this->commands;
            $commands = array_unique(array_filter($commands, function ($nameOrAlias) use($commandList, $commands, &$aliases) {
                $commandName = $commandList[$nameOrAlias] instanceof Command ? $commandList[$nameOrAlias]->getName() : $nameOrAlias;
                $aliases[$nameOrAlias] = $commandName;
                return $commandName === $nameOrAlias || !\in_array($commandName, $commands);
            }));
        }
        $exact = \in_array($name, $commands, true) || isset($aliases[$name]);
        if (\count($commands) > 1 && !$exact) {
            $usableWidth = $this->terminal->getWidth() - 10;
            $abbrevs = array_values($commands);
            $maxLen = 0;
            foreach ($abbrevs as $abbrev) {
                $maxLen = max(Helper::strlen($abbrev), $maxLen);
            }
            $abbrevs = array_map(function ($cmd) use($commandList, $usableWidth, $maxLen) {
                if (!$commandList[$cmd] instanceof Command) {
                    return $cmd;
                }
                $abbrev = str_pad($cmd, $maxLen, ' ') . ' ' . $commandList[$cmd]->getDescription();
                return Helper::strlen($abbrev) > $usableWidth ? Helper::substr($abbrev, 0, $usableWidth - 3) . '...' : $abbrev;
            }, array_values($commands));
            $suggestions = $this->getAbbreviationSuggestions($abbrevs);
            throw new CommandNotFoundException(sprintf("Command \"%s\" is ambiguous.\nDid you mean one of these?\n%s", $name, $suggestions), array_values($commands));
        }
        return $this->get($exact ? $name : reset($commands));
    }
    public function all($namespace = null)
    {
        $this->init();
        if (null === $namespace) {
            if (!$this->commandLoader) {
                return $this->commands;
            }
            $commands = $this->commands;
            foreach ($this->commandLoader->getNames() as $name) {
                if (!isset($commands[$name]) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
            return $commands;
        }
        $commands = array();
        foreach ($this->commands as $name => $command) {
            if ($namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1)) {
                $commands[$name] = $command;
            }
        }
        if ($this->commandLoader) {
            foreach ($this->commandLoader->getNames() as $name) {
                if (!isset($commands[$name]) && $namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
        }
        return $commands;
    }
    public static function getAbbreviations($names)
    {
        $abbrevs = array();
        foreach ($names as $name) {
            for ($len = \strlen($name); $len > 0; --$len) {
                $abbrev = substr($name, 0, $len);
                $abbrevs[$abbrev][] = $name;
            }
        }
        return $abbrevs;
    }
    public function renderException(\Exception $e, OutputInterface $output)
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        $this->doRenderException($e, $output);
        if (null !== $this->runningCommand) {
            $output->writeln(sprintf('<info>%s</info>', sprintf($this->runningCommand->getSynopsis(), $this->getName())), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }
    }
    protected function doRenderException(\Exception $e, OutputInterface $output)
    {
        do {
            $message = trim($e->getMessage());
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $title = sprintf('  [%s%s]  ', \get_class($e), 0 !== ($code = $e->getCode()) ? ' (' . $code . ')' : '');
                $len = Helper::strlen($title);
            } else {
                $len = 0;
            }
            $width = $this->terminal->getWidth() ? $this->terminal->getWidth() - 1 : PHP_INT_MAX;
            if (\defined('HHVM_VERSION') && $width > 1 << 31) {
                $width = 1 << 31;
            }
            $lines = array();
            foreach ('' !== $message ? preg_split('/\\r?\\n/', $message) : array() as $line) {
                foreach ($this->splitStringByWidth($line, $width - 4) as $line) {
                    $lineLength = Helper::strlen($line) + 4;
                    $lines[] = array($line, $lineLength);
                    $len = max($lineLength, $len);
                }
            }
            $messages = array();
            if (!$e instanceof ExceptionInterface || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = sprintf('<comment>%s</comment>', OutputFormatter::escape(sprintf('In %s line %s:', basename($e->getFile()) ?: 'n/a', $e->getLine() ?: 'n/a')));
            }
            $messages[] = $emptyLine = sprintf('<error>%s</error>', str_repeat(' ', $len));
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = sprintf('<error>%s%s</error>', $title, str_repeat(' ', max(0, $len - Helper::strlen($title))));
            }
            foreach ($lines as $line) {
                $messages[] = sprintf('<error>  %s  %s</error>', OutputFormatter::escape($line[0]), str_repeat(' ', $len - $line[1]));
            }
            $messages[] = $emptyLine;
            $messages[] = '';
            $output->writeln($messages, OutputInterface::VERBOSITY_QUIET);
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);
                $trace = $e->getTrace();
                array_unshift($trace, array('function' => '', 'file' => $e->getFile() ?: 'n/a', 'line' => $e->getLine() ?: 'n/a', 'args' => array()));
                for ($i = 0, $count = \count($trace); $i < $count; ++$i) {
                    $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
                    $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
                    $function = $trace[$i]['function'];
                    $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
                    $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';
                    $output->writeln(sprintf(' %s%s%s() at <info>%s:%s</info>', $class, $type, $function, $file, $line), OutputInterface::VERBOSITY_QUIET);
                }
                $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            }
        } while ($e = $e->getPrevious());
    }
    protected function getTerminalWidth()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);
        return $this->terminal->getWidth();
    }
    protected function getTerminalHeight()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);
        return $this->terminal->getHeight();
    }
    public function getTerminalDimensions()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);
        return array($this->terminal->getWidth(), $this->terminal->getHeight());
    }
    public function setTerminalDimensions($width, $height)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated as of 3.2 and will be removed in 4.0. Set the COLUMNS and LINES env vars instead.', __METHOD__), E_USER_DEPRECATED);
        putenv('COLUMNS=' . $width);
        putenv('LINES=' . $height);
        return $this;
    }
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--ansi'), true)) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(array('--no-ansi'), true)) {
            $output->setDecorated(false);
        }
        if (true === $input->hasParameterOption(array('--no-interaction', '-n'), true)) {
            $input->setInteractive(false);
        } elseif (\function_exists('posix_isatty')) {
            $inputStream = null;
            if ($input instanceof StreamableInputInterface) {
                $inputStream = $input->getStream();
            }
            if (!$inputStream && $this->getHelperSet()->has('question')) {
                $inputStream = $this->getHelperSet()->get('question')->getInputStream(false);
            }
            if (!@posix_isatty($inputStream) && false === getenv('SHELL_INTERACTIVE')) {
                $input->setInteractive(false);
            }
        }
        switch ($shellVerbosity = (int) getenv('SHELL_VERBOSITY')) {
            case -1:
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
                break;
            case 1:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                break;
            case 2:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;
            case 3:
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                break;
            default:
                $shellVerbosity = 0;
                break;
        }
        if (true === $input->hasParameterOption(array('--quiet', '-q'), true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $shellVerbosity = -1;
        } else {
            if ($input->hasParameterOption('-vvv', true) || $input->hasParameterOption('--verbose=3', true) || 3 === $input->getParameterOption('--verbose', false, true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                $shellVerbosity = 3;
            } elseif ($input->hasParameterOption('-vv', true) || $input->hasParameterOption('--verbose=2', true) || 2 === $input->getParameterOption('--verbose', false, true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                $shellVerbosity = 2;
            } elseif ($input->hasParameterOption('-v', true) || $input->hasParameterOption('--verbose=1', true) || $input->hasParameterOption('--verbose', true) || $input->getParameterOption('--verbose', false, true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $shellVerbosity = 1;
            }
        }
        if (-1 === $shellVerbosity) {
            $input->setInteractive(false);
        }
        putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        foreach ($command->getHelperSet() as $helper) {
            if ($helper instanceof InputAwareInterface) {
                $helper->setInput($input);
            }
        }
        if (null === $this->dispatcher) {
            return $command->run($input, $output);
        }
        try {
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        } catch (ExceptionInterface $e) {
        }
        $event = new ConsoleCommandEvent($command, $input, $output);
        $e = null;
        try {
            $this->dispatcher->dispatch(ConsoleEvents::COMMAND, $event);
            if ($event->commandShouldRun()) {
                $exitCode = $command->run($input, $output);
            } else {
                $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
            }
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }
        if (null !== $e) {
            if ($this->dispatcher->hasListeners(ConsoleEvents::EXCEPTION)) {
                $x = $e instanceof \Exception ? $e : new FatalThrowableError($e);
                $event = new ConsoleExceptionEvent($command, $input, $output, $x, $x->getCode());
                $this->dispatcher->dispatch(ConsoleEvents::EXCEPTION, $event);
                if ($x !== $event->getException()) {
                    $e = $event->getException();
                }
            }
            $event = new ConsoleErrorEvent($input, $output, $e, $command);
            $this->dispatcher->dispatch(ConsoleEvents::ERROR, $event);
            $e = $event->getError();
            if (0 === ($exitCode = $event->getExitCode())) {
                $e = null;
            }
        }
        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);
        $this->dispatcher->dispatch(ConsoleEvents::TERMINATE, $event);
        if (null !== $e) {
            throw $e;
        }
        return $event->getExitCode();
    }
    protected function getCommandName(InputInterface $input)
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'), new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'), new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'), new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'), new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'), new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'), new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'), new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question')));
    }
    protected function getDefaultCommands()
    {
        return array(new HelpCommand(), new ListCommand());
    }
    protected function getDefaultHelperSet()
    {
        return new HelperSet(array(new FormatterHelper(), new DebugFormatterHelper(), new ProcessHelper(), new QuestionHelper()));
    }
    private function getAbbreviationSuggestions($abbrevs)
    {
        return '    ' . implode("\n    ", $abbrevs);
    }
    public function extractNamespace($name, $limit = null)
    {
        $parts = explode(':', $name);
        array_pop($parts);
        return implode(':', null === $limit ? $parts : \array_slice($parts, 0, $limit));
    }
    private function findAlternatives($name, $collection)
    {
        $threshold = 1000.0;
        $alternatives = array();
        $collectionParts = array();
        foreach ($collection as $item) {
            $collectionParts[$item] = explode(':', $item);
        }
        foreach (explode(':', $name) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }
                $lev = levenshtein($subname, $parts[$i]);
                if ($lev <= \strlen($subname) / 3 || '' !== $subname && false !== strpos($parts[$i], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }
        foreach ($collection as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= \strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev;
            }
        }
        $alternatives = array_filter($alternatives, function ($lev) use($threshold) {
            return $lev < 2 * $threshold;
        });
        ksort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);
        return array_keys($alternatives);
    }
    public function setDefaultCommand($commandName, $isSingleCommand = false)
    {
        $this->defaultCommand = $commandName;
        if ($isSingleCommand) {
            $this->find($commandName);
            $this->singleCommand = true;
        }
        return $this;
    }
    public function isSingleCommand()
    {
        return $this->singleCommand;
    }
    private function splitStringByWidth($string, $width)
    {
        if (false === ($encoding = mb_detect_encoding($string, null, true))) {
            return str_split($string, $width);
        }
        $utf8String = mb_convert_encoding($string, 'utf8', $encoding);
        $lines = array();
        $line = '';
        foreach (preg_split('//u', $utf8String) as $char) {
            if (mb_strwidth($line . $char, 'utf8') <= $width) {
                $line .= $char;
                continue;
            }
            $lines[] = str_pad($line, $width);
            $line = $char;
        }
        $lines[] = \count($lines) ? str_pad($line, $width) : $line;
        mb_convert_variables($encoding, 'utf8', $lines);
        return $lines;
    }
    private function extractAllNamespaces($name)
    {
        $parts = explode(':', $name, -1);
        $namespaces = array();
        foreach ($parts as $part) {
            if (\count($namespaces)) {
                $namespaces[] = end($namespaces) . ':' . $part;
            } else {
                $namespaces[] = $part;
            }
        }
        return $namespaces;
    }
    private function init()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        foreach ($this->getDefaultCommands() as $command) {
            $this->add($command);
        }
    }
}
}

namespace Symfony\Component\Console {
class Terminal
{
    private static $width;
    private static $height;
    public function getWidth()
    {
        $width = getenv('COLUMNS');
        if (false !== $width) {
            return (int) trim($width);
        }
        if (null === self::$width) {
            self::initDimensions();
        }
        return self::$width ?: 80;
    }
    public function getHeight()
    {
        $height = getenv('LINES');
        if (false !== $height) {
            return (int) trim($height);
        }
        if (null === self::$height) {
            self::initDimensions();
        }
        return self::$height ?: 50;
    }
    private static function initDimensions()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            if (preg_match('/^(\\d+)x(\\d+)(?: \\((\\d+)x(\\d+)\\))?$/', trim(getenv('ANSICON')), $matches)) {
                self::$width = (int) $matches[1];
                self::$height = isset($matches[4]) ? (int) $matches[4] : (int) $matches[2];
            } elseif (null !== ($dimensions = self::getConsoleMode())) {
                self::$width = (int) $dimensions[0];
                self::$height = (int) $dimensions[1];
            }
        } elseif ($sttyString = self::getSttyColumns()) {
            if (preg_match('/rows.(\\d+);.columns.(\\d+);/i', $sttyString, $matches)) {
                self::$width = (int) $matches[2];
                self::$height = (int) $matches[1];
            } elseif (preg_match('/;.(\\d+).rows;.(\\d+).columns/i', $sttyString, $matches)) {
                self::$width = (int) $matches[2];
                self::$height = (int) $matches[1];
            }
        }
    }
    private static function getConsoleMode()
    {
        if (!\function_exists('proc_open')) {
            return;
        }
        $descriptorspec = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $process = proc_open('mode CON', $descriptorspec, $pipes, null, null, array('suppress_errors' => true));
        if (\is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if (preg_match('/--------+\\r?\\n.+?(\\d+)\\r?\\n.+?(\\d+)\\r?\\n/', $info, $matches)) {
                return array((int) $matches[2], (int) $matches[1]);
            }
        }
    }
    private static function getSttyColumns()
    {
        if (!\function_exists('proc_open')) {
            return;
        }
        $descriptorspec = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $process = proc_open('stty -a | grep columns', $descriptorspec, $pipes, null, null, array('suppress_errors' => true));
        if (\is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return $info;
        }
    }
}
}

namespace Symfony\Component\Console\Command {
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class HelpCommand extends Command
{
    private $command;
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this->setName('help')->setDefinition(array(new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help'), new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'), new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command help')))->setDescription('Displays help for a command')->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays help for a given command:
  <info>php %command.full_name% list</info>
You can also output the help in other formats by using the <comment>--format</comment> option:
  <info>php %command.full_name% --format=xml list</info>
To display the list of available commands, please use the <info>list</info> command.
EOF
);
    }
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, array('format' => $input->getOption('format'), 'raw_text' => $input->getOption('raw')));
        $this->command = null;
    }
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
class InputArgument
{
    const REQUIRED = 1;
    const OPTIONAL = 2;
    const IS_ARRAY = 4;
    private $name;
    private $mode;
    private $default;
    private $description;
    public function __construct($name, $mode = null, $description = '', $default = null)
    {
        if (null === $mode) {
            $mode = self::OPTIONAL;
        } elseif (!\is_int($mode) || $mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(sprintf('Argument mode "%s" is not valid.', $mode));
        }
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
        $this->setDefault($default);
    }
    public function getName()
    {
        return $this->name;
    }
    public function isRequired()
    {
        return self::REQUIRED === (self::REQUIRED & $this->mode);
    }
    public function isArray()
    {
        return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
    }
    public function setDefault($default = null)
    {
        if (self::REQUIRED === $this->mode && null !== $default) {
            throw new LogicException('Cannot set a default value except for InputArgument::OPTIONAL mode.');
        }
        if ($this->isArray()) {
            if (null === $default) {
                $default = array();
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array argument must be an array.');
            }
        }
        $this->default = $default;
    }
    public function getDefault()
    {
        return $this->default;
    }
    public function getDescription()
    {
        return $this->description;
    }
}
}

namespace Symfony\Component\Console\Command {
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class ListCommand extends Command
{
    protected function configure()
    {
        $this->setName('list')->setDefinition($this->createDefinition())->setDescription('Lists commands')->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all commands:
  <info>php %command.full_name%</info>
You can also display the commands for a specific namespace:
  <info>php %command.full_name% test</info>
You can also output the information in other formats by using the <comment>--format</comment> option:
  <info>php %command.full_name% --format=xml</info>
It's also possible to get raw list of commands (useful for embedding command runner):
  <info>php %command.full_name% --raw</info>
EOF
);
    }
    public function getNativeDefinition()
    {
        return $this->createDefinition();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->getApplication(), array('format' => $input->getOption('format'), 'raw_text' => $input->getOption('raw'), 'namespace' => $input->getArgument('namespace')));
    }
    private function createDefinition()
    {
        return new InputDefinition(array(new InputArgument('namespace', InputArgument::OPTIONAL, 'The namespace name'), new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command list'), new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt')));
    }
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
class HelperSet implements \IteratorAggregate
{
    private $helpers = array();
    private $command;
    public function __construct(array $helpers = array())
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, \is_int($alias) ? null : $alias);
        }
    }
    public function set(HelperInterface $helper, $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }
        $helper->setHelperSet($this);
    }
    public function has($name)
    {
        return isset($this->helpers[$name]);
    }
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name));
        }
        return $this->helpers[$name];
    }
    public function setCommand(Command $command = null)
    {
        $this->command = $command;
    }
    public function getCommand()
    {
        return $this->command;
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->helpers);
    }
}
}

namespace Symfony\Component\Console\Helper {
interface HelperInterface
{
    public function setHelperSet(HelperSet $helperSet = null);
    public function getHelperSet();
    public function getName();
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
abstract class Helper implements HelperInterface
{
    protected $helperSet = null;
    public function setHelperSet(HelperSet $helperSet = null)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet()
    {
        return $this->helperSet;
    }
    public static function strlen($string)
    {
        if (false === ($encoding = mb_detect_encoding($string, null, true))) {
            return \strlen($string);
        }
        return mb_strwidth($string, $encoding);
    }
    public static function substr($string, $from, $length = null)
    {
        if (false === ($encoding = mb_detect_encoding($string, null, true))) {
            return substr($string, $from, $length);
        }
        return mb_substr($string, $from, $length, $encoding);
    }
    public static function formatTime($secs)
    {
        static $timeFormats = array(array(0, '< 1 sec'), array(1, '1 sec'), array(2, 'secs', 1), array(60, '1 min'), array(120, 'mins', 60), array(3600, '1 hr'), array(7200, 'hrs', 3600), array(86400, '1 day'), array(172800, 'days', 86400));
        foreach ($timeFormats as $index => $format) {
            if ($secs >= $format[0]) {
                if (isset($timeFormats[$index + 1]) && $secs < $timeFormats[$index + 1][0] || $index == \count($timeFormats) - 1) {
                    if (2 == \count($format)) {
                        return $format[1];
                    }
                    return floor($secs / $format[2]) . ' ' . $format[1];
                }
            }
        }
    }
    public static function formatMemory($memory)
    {
        if ($memory >= 1024 * 1024 * 1024) {
            return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);
        }
        if ($memory >= 1024 * 1024) {
            return sprintf('%.1f MiB', $memory / 1024 / 1024);
        }
        if ($memory >= 1024) {
            return sprintf('%d KiB', $memory / 1024);
        }
        return sprintf('%d B', $memory);
    }
    public static function strlenWithoutDecoration(OutputFormatterInterface $formatter, $string)
    {
        return self::strlen(self::removeDecoration($formatter, $string));
    }
    public static function removeDecoration(OutputFormatterInterface $formatter, $string)
    {
        $isDecorated = $formatter->isDecorated();
        $formatter->setDecorated(false);
        $string = $formatter->format($string);
        $string = preg_replace("/\33\\[[^m]*m/", '', $string);
        $formatter->setDecorated($isDecorated);
        return $string;
    }
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Formatter\OutputFormatter;
class FormatterHelper extends Helper
{
    public function formatSection($section, $message, $style = 'info')
    {
        return sprintf('<%s>[%s]</%s> %s', $style, $section, $style, $message);
    }
    public function formatBlock($messages, $style, $large = false)
    {
        if (!\is_array($messages)) {
            $messages = array($messages);
        }
        $len = 0;
        $lines = array();
        foreach ($messages as $message) {
            $message = OutputFormatter::escape($message);
            $lines[] = sprintf($large ? '  %s  ' : ' %s ', $message);
            $len = max($this->strlen($message) + ($large ? 4 : 2), $len);
        }
        $messages = $large ? array(str_repeat(' ', $len)) : array();
        for ($i = 0; isset($lines[$i]); ++$i) {
            $messages[] = $lines[$i] . str_repeat(' ', $len - $this->strlen($lines[$i]));
        }
        if ($large) {
            $messages[] = str_repeat(' ', $len);
        }
        for ($i = 0; isset($messages[$i]); ++$i) {
            $messages[$i] = sprintf('<%s>%s</%s>', $style, $messages[$i], $style);
        }
        return implode("\n", $messages);
    }
    public function truncate($message, $length, $suffix = '...')
    {
        $computedLength = $length - $this->strlen($suffix);
        if ($computedLength > $this->strlen($message)) {
            return $message;
        }
        if (false === ($encoding = mb_detect_encoding($message, null, true))) {
            return substr($message, 0, $length) . $suffix;
        }
        return mb_substr($message, 0, $length, $encoding) . $suffix;
    }
    public function getName()
    {
        return 'formatter';
    }
}
}

namespace Symfony\Component\Console\Helper {
class DebugFormatterHelper extends Helper
{
    private $colors = array('black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default');
    private $started = array();
    private $count = -1;
    public function start($id, $message, $prefix = 'RUN')
    {
        $this->started[$id] = array('border' => ++$this->count % \count($this->colors));
        return sprintf("%s<bg=blue;fg=white> %s </> <fg=blue>%s</>\n", $this->getBorder($id), $prefix, $message);
    }
    public function progress($id, $buffer, $error = false, $prefix = 'OUT', $errorPrefix = 'ERR')
    {
        $message = '';
        if ($error) {
            if (isset($this->started[$id]['out'])) {
                $message .= "\n";
                unset($this->started[$id]['out']);
            }
            if (!isset($this->started[$id]['err'])) {
                $message .= sprintf('%s<bg=red;fg=white> %s </> ', $this->getBorder($id), $errorPrefix);
                $this->started[$id]['err'] = true;
            }
            $message .= str_replace("\n", sprintf("\n%s<bg=red;fg=white> %s </> ", $this->getBorder($id), $errorPrefix), $buffer);
        } else {
            if (isset($this->started[$id]['err'])) {
                $message .= "\n";
                unset($this->started[$id]['err']);
            }
            if (!isset($this->started[$id]['out'])) {
                $message .= sprintf('%s<bg=green;fg=white> %s </> ', $this->getBorder($id), $prefix);
                $this->started[$id]['out'] = true;
            }
            $message .= str_replace("\n", sprintf("\n%s<bg=green;fg=white> %s </> ", $this->getBorder($id), $prefix), $buffer);
        }
        return $message;
    }
    public function stop($id, $message, $successful, $prefix = 'RES')
    {
        $trailingEOL = isset($this->started[$id]['out']) || isset($this->started[$id]['err']) ? "\n" : '';
        if ($successful) {
            return sprintf("%s%s<bg=green;fg=white> %s </> <fg=green>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);
        }
        $message = sprintf("%s%s<bg=red;fg=white> %s </> <fg=red>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);
        unset($this->started[$id]['out'], $this->started[$id]['err']);
        return $message;
    }
    private function getBorder($id)
    {
        return sprintf('<bg=%s> </>', $this->colors[$this->started[$id]['border']]);
    }
    public function getName()
    {
        return 'debug_formatter';
    }
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
class ProcessHelper extends Helper
{
    public function run(OutputInterface $output, $cmd, $error = null, callable $callback = null, $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        if ($cmd instanceof Process) {
            $process = $cmd;
        } else {
            $process = new Process($cmd);
        }
        if ($verbosity <= $output->getVerbosity()) {
            $output->write($formatter->start(spl_object_hash($process), $this->escapeString($process->getCommandLine())));
        }
        if ($output->isDebug()) {
            $callback = $this->wrapCallback($output, $process, $callback);
        }
        $process->run($callback);
        if ($verbosity <= $output->getVerbosity()) {
            $message = $process->isSuccessful() ? 'Command ran successfully' : sprintf('%s Command did not run successfully', $process->getExitCode());
            $output->write($formatter->stop(spl_object_hash($process), $message, $process->isSuccessful()));
        }
        if (!$process->isSuccessful() && null !== $error) {
            $output->writeln(sprintf('<error>%s</error>', $this->escapeString($error)));
        }
        return $process;
    }
    public function mustRun(OutputInterface $output, $cmd, $error = null, callable $callback = null)
    {
        $process = $this->run($output, $cmd, $error, $callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process;
    }
    public function wrapCallback(OutputInterface $output, Process $process, callable $callback = null)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        return function ($type, $buffer) use($output, $process, $callback, $formatter) {
            $output->write($formatter->progress(spl_object_hash($process), $this->escapeString($buffer), Process::ERR === $type));
            if (null !== $callback) {
                \call_user_func($callback, $type, $buffer);
            }
        };
    }
    private function escapeString($str)
    {
        return str_replace('<', '\\<', $str);
    }
    public function getName()
    {
        return 'process';
    }
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
class QuestionHelper extends Helper
{
    private $inputStream;
    private static $shell;
    private static $stty;
    public function ask(InputInterface $input, OutputInterface $output, Question $question)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        if (!$input->isInteractive()) {
            $default = $question->getDefault();
            if (null !== $default && $question instanceof ChoiceQuestion) {
                $choices = $question->getChoices();
                if (!$question->isMultiselect()) {
                    return isset($choices[$default]) ? $choices[$default] : $default;
                }
                $default = explode(',', $default);
                foreach ($default as $k => $v) {
                    $v = trim($v);
                    $default[$k] = isset($choices[$v]) ? $choices[$v] : $v;
                }
            }
            return $default;
        }
        if ($input instanceof StreamableInputInterface && ($stream = $input->getStream())) {
            $this->inputStream = $stream;
        }
        if (!$question->getValidator()) {
            return $this->doAsk($output, $question);
        }
        $interviewer = function () use($output, $question) {
            return $this->doAsk($output, $question);
        };
        return $this->validateAttempts($interviewer, $output, $question);
    }
    public function setInputStream($stream)
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.2 and will be removed in 4.0. Use %s::setStream() instead.', __METHOD__, StreamableInputInterface::class), E_USER_DEPRECATED);
        if (!\is_resource($stream)) {
            throw new InvalidArgumentException('Input stream must be a valid resource.');
        }
        $this->inputStream = $stream;
    }
    public function getInputStream()
    {
        if (0 === \func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.2 and will be removed in 4.0. Use %s::getStream() instead.', __METHOD__, StreamableInputInterface::class), E_USER_DEPRECATED);
        }
        return $this->inputStream;
    }
    public function getName()
    {
        return 'question';
    }
    public static function disableStty()
    {
        self::$stty = false;
    }
    private function doAsk(OutputInterface $output, Question $question)
    {
        $this->writePrompt($output, $question);
        $inputStream = $this->inputStream ?: STDIN;
        $autocomplete = $question->getAutocompleterValues();
        if (null === $autocomplete || !$this->hasSttyAvailable()) {
            $ret = false;
            if ($question->isHidden()) {
                try {
                    $ret = trim($this->getHiddenResponse($output, $inputStream));
                } catch (RuntimeException $e) {
                    if (!$question->isHiddenFallback()) {
                        throw $e;
                    }
                }
            }
            if (false === $ret) {
                $ret = fgets($inputStream, 4096);
                if (false === $ret) {
                    throw new RuntimeException('Aborted');
                }
                $ret = trim($ret);
            }
        } else {
            $ret = trim($this->autocomplete($output, $question, $inputStream, \is_array($autocomplete) ? $autocomplete : iterator_to_array($autocomplete, false)));
        }
        $ret = \strlen($ret) > 0 ? $ret : $question->getDefault();
        if ($normalizer = $question->getNormalizer()) {
            return $normalizer($ret);
        }
        return $ret;
    }
    protected function writePrompt(OutputInterface $output, Question $question)
    {
        $message = $question->getQuestion();
        if ($question instanceof ChoiceQuestion) {
            $maxWidth = max(array_map(array($this, 'strlen'), array_keys($question->getChoices())));
            $messages = (array) $question->getQuestion();
            foreach ($question->getChoices() as $key => $value) {
                $width = $maxWidth - $this->strlen($key);
                $messages[] = '  [<info>' . $key . str_repeat(' ', $width) . '</info>] ' . $value;
            }
            $output->writeln($messages);
            $message = $question->getPrompt();
        }
        $output->write($message);
    }
    protected function writeError(OutputInterface $output, \Exception $error)
    {
        if (null !== $this->getHelperSet() && $this->getHelperSet()->has('formatter')) {
            $message = $this->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error');
        } else {
            $message = '<error>' . $error->getMessage() . '</error>';
        }
        $output->writeln($message);
    }
    private function autocomplete(OutputInterface $output, Question $question, $inputStream, array $autocomplete)
    {
        $ret = '';
        $i = 0;
        $ofs = -1;
        $matches = $autocomplete;
        $numMatches = \count($matches);
        $sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');
        $output->getFormatter()->setStyle('hl', new OutputFormatterStyle('black', 'white'));
        while (!feof($inputStream)) {
            $c = fread($inputStream, 1);
            if ("" === $c) {
                if (0 === $numMatches && 0 !== $i) {
                    --$i;
                    $output->write("\33[1D");
                }
                if (0 === $i) {
                    $ofs = -1;
                    $matches = $autocomplete;
                    $numMatches = \count($matches);
                } else {
                    $numMatches = 0;
                }
                $ret = substr($ret, 0, $i);
            } elseif ("\33" === $c) {
                $c .= fread($inputStream, 2);
                if (isset($c[2]) && ('A' === $c[2] || 'B' === $c[2])) {
                    if ('A' === $c[2] && -1 === $ofs) {
                        $ofs = 0;
                    }
                    if (0 === $numMatches) {
                        continue;
                    }
                    $ofs += 'A' === $c[2] ? -1 : 1;
                    $ofs = ($numMatches + $ofs) % $numMatches;
                }
            } elseif (\ord($c) < 32) {
                if ("\t" === $c || "\n" === $c) {
                    if ($numMatches > 0 && -1 !== $ofs) {
                        $ret = $matches[$ofs];
                        $output->write(substr($ret, $i));
                        $i = \strlen($ret);
                    }
                    if ("\n" === $c) {
                        $output->write($c);
                        break;
                    }
                    $numMatches = 0;
                }
                continue;
            } else {
                $output->write($c);
                $ret .= $c;
                ++$i;
                $numMatches = 0;
                $ofs = 0;
                foreach ($autocomplete as $value) {
                    if (0 === strpos($value, $ret)) {
                        $matches[$numMatches++] = $value;
                    }
                }
            }
            $output->write("\33[K");
            if ($numMatches > 0 && -1 !== $ofs) {
                $output->write("\0337");
                $output->write('<hl>' . OutputFormatter::escapeTrailingBackslash(substr($matches[$ofs], $i)) . '</hl>');
                $output->write("\338");
            }
        }
        shell_exec(sprintf('stty %s', $sttyMode));
        return $ret;
    }
    private function getHiddenResponse(OutputInterface $output, $inputStream)
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $exe = '/builds/apliteni/keitaro/install.php/vendor/symfony/console/Helper' . '/../Resources/bin/hiddeninput.exe';
            if ('phar:' === substr('/builds/apliteni/keitaro/install.php/vendor/symfony/console/Helper/QuestionHelper.php', 0, 5)) {
                $tmpExe = sys_get_temp_dir() . '/hiddeninput.exe';
                copy($exe, $tmpExe);
                $exe = $tmpExe;
            }
            $value = rtrim(shell_exec($exe));
            $output->writeln('');
            if (isset($tmpExe)) {
                unlink($tmpExe);
            }
            return $value;
        }
        if ($this->hasSttyAvailable()) {
            $sttyMode = shell_exec('stty -g');
            shell_exec('stty -echo');
            $value = fgets($inputStream, 4096);
            shell_exec(sprintf('stty %s', $sttyMode));
            if (false === $value) {
                throw new RuntimeException('Aborted');
            }
            $value = trim($value);
            $output->writeln('');
            return $value;
        }
        if (false !== ($shell = $this->getShell())) {
            $readCmd = 'csh' === $shell ? 'set mypassword = $<' : 'read -r mypassword';
            $command = sprintf("/usr/bin/env %s -c 'stty -echo; %s; stty echo; echo \$mypassword'", $shell, $readCmd);
            $value = rtrim(shell_exec($command));
            $output->writeln('');
            return $value;
        }
        throw new RuntimeException('Unable to hide the response.');
    }
    private function validateAttempts(callable $interviewer, OutputInterface $output, Question $question)
    {
        $error = null;
        $attempts = $question->getMaxAttempts();
        while (null === $attempts || $attempts--) {
            if (null !== $error) {
                $this->writeError($output, $error);
            }
            try {
                return \call_user_func($question->getValidator(), $interviewer());
            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $error) {
            }
        }
        throw $error;
    }
    private function getShell()
    {
        if (null !== self::$shell) {
            return self::$shell;
        }
        self::$shell = false;
        if (file_exists('/usr/bin/env')) {
            $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";
            foreach (array('bash', 'zsh', 'ksh', 'csh') as $sh) {
                if ('OK' === rtrim(shell_exec(sprintf($test, $sh)))) {
                    self::$shell = $sh;
                    break;
                }
            }
        }
        return self::$shell;
    }
    private function hasSttyAvailable()
    {
        if (null !== self::$stty) {
            return self::$stty;
        }
        exec('stty 2>&1', $output, $exitcode);
        return self::$stty = 0 === $exitcode;
    }
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
interface InputInterface
{
    public function getFirstArgument();
    public function hasParameterOption($values, $onlyParams = false);
    public function getParameterOption($values, $default = false, $onlyParams = false);
    public function bind(InputDefinition $definition);
    public function validate();
    public function getArguments();
    public function getArgument($name);
    public function setArgument($name, $value);
    public function hasArgument($name);
    public function getOptions();
    public function getOption($name);
    public function setOption($name, $value);
    public function hasOption($name);
    public function isInteractive();
    public function setInteractive($interactive);
}
}

namespace Symfony\Component\Console\Input {
interface StreamableInputInterface extends InputInterface
{
    public function setStream($stream);
    public function getStream();
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
abstract class Input implements InputInterface, StreamableInputInterface
{
    protected $definition;
    protected $stream;
    protected $options = array();
    protected $arguments = array();
    protected $interactive = true;
    public function __construct(InputDefinition $definition = null)
    {
        if (null === $definition) {
            $this->definition = new InputDefinition();
        } else {
            $this->bind($definition);
            $this->validate();
        }
    }
    public function bind(InputDefinition $definition)
    {
        $this->arguments = array();
        $this->options = array();
        $this->definition = $definition;
        $this->parse();
    }
    protected abstract function parse();
    public function validate()
    {
        $definition = $this->definition;
        $givenArguments = $this->arguments;
        $missingArguments = array_filter(array_keys($definition->getArguments()), function ($argument) use($definition, $givenArguments) {
            return !array_key_exists($argument, $givenArguments) && $definition->getArgument($argument)->isRequired();
        });
        if (\count($missingArguments) > 0) {
            throw new RuntimeException(sprintf('Not enough arguments (missing: "%s").', implode(', ', $missingArguments)));
        }
    }
    public function isInteractive()
    {
        return $this->interactive;
    }
    public function setInteractive($interactive)
    {
        $this->interactive = (bool) $interactive;
    }
    public function getArguments()
    {
        return array_merge($this->definition->getArgumentDefaults(), $this->arguments);
    }
    public function getArgument($name)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        return isset($this->arguments[$name]) ? $this->arguments[$name] : $this->definition->getArgument($name)->getDefault();
    }
    public function setArgument($name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        $this->arguments[$name] = $value;
    }
    public function hasArgument($name)
    {
        return $this->definition->hasArgument($name);
    }
    public function getOptions()
    {
        return array_merge($this->definition->getOptionDefaults(), $this->options);
    }
    public function getOption($name)
    {
        if (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }
        return array_key_exists($name, $this->options) ? $this->options[$name] : $this->definition->getOption($name)->getDefault();
    }
    public function setOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }
        $this->options[$name] = $value;
    }
    public function hasOption($name)
    {
        return $this->definition->hasOption($name);
    }
    public function escapeToken($token)
    {
        return preg_match('{^[\\w-]+$}', $token) ? $token : escapeshellarg($token);
    }
    public function setStream($stream)
    {
        $this->stream = $stream;
    }
    public function getStream()
    {
        return $this->stream;
    }
}
}

namespace Symfony\Component\Console\Input {
use Symfony\Component\Console\Exception\RuntimeException;
class ArgvInput extends Input
{
    private $tokens;
    private $parsed;
    public function __construct(array $argv = null, InputDefinition $definition = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }
        array_shift($argv);
        $this->tokens = $argv;
        parent::__construct($definition);
    }
    protected function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }
    protected function parse()
    {
        $parseOptions = true;
        $this->parsed = $this->tokens;
        while (null !== ($token = array_shift($this->parsed))) {
            if ($parseOptions && '' == $token) {
                $this->parseArgument($token);
            } elseif ($parseOptions && '--' == $token) {
                $parseOptions = false;
            } elseif ($parseOptions && 0 === strpos($token, '--')) {
                $this->parseLongOption($token);
            } elseif ($parseOptions && '-' === $token[0] && '-' !== $token) {
                $this->parseShortOption($token);
            } else {
                $this->parseArgument($token);
            }
        }
    }
    private function parseShortOption($token)
    {
        $name = substr($token, 1);
        if (\strlen($name) > 1) {
            if ($this->definition->hasShortcut($name[0]) && $this->definition->getOptionForShortcut($name[0])->acceptValue()) {
                $this->addShortOption($name[0], substr($name, 1));
            } else {
                $this->parseShortOptionSet($name);
            }
        } else {
            $this->addShortOption($name, null);
        }
    }
    private function parseShortOptionSet($name)
    {
        $len = \strlen($name);
        for ($i = 0; $i < $len; ++$i) {
            if (!$this->definition->hasShortcut($name[$i])) {
                $encoding = mb_detect_encoding($name, null, true);
                throw new RuntimeException(sprintf('The "-%s" option does not exist.', false === $encoding ? $name[$i] : mb_substr($name, $i, 1, $encoding)));
            }
            $option = $this->definition->getOptionForShortcut($name[$i]);
            if ($option->acceptValue()) {
                $this->addLongOption($option->getName(), $i === $len - 1 ? null : substr($name, $i + 1));
                break;
            } else {
                $this->addLongOption($option->getName(), null);
            }
        }
    }
    private function parseLongOption($token)
    {
        $name = substr($token, 2);
        if (false !== ($pos = strpos($name, '='))) {
            if (0 === \strlen($value = substr($name, $pos + 1))) {
                if (\PHP_VERSION_ID < 70000 && false === $value) {
                    $value = '';
                }
                array_unshift($this->parsed, $value);
            }
            $this->addLongOption(substr($name, 0, $pos), $value);
        } else {
            $this->addLongOption($name, null);
        }
    }
    private function parseArgument($token)
    {
        $c = \count($this->arguments);
        if ($this->definition->hasArgument($c)) {
            $arg = $this->definition->getArgument($c);
            $this->arguments[$arg->getName()] = $arg->isArray() ? array($token) : $token;
        } elseif ($this->definition->hasArgument($c - 1) && $this->definition->getArgument($c - 1)->isArray()) {
            $arg = $this->definition->getArgument($c - 1);
            $this->arguments[$arg->getName()][] = $token;
        } else {
            $all = $this->definition->getArguments();
            if (\count($all)) {
                throw new RuntimeException(sprintf('Too many arguments, expected arguments "%s".', implode('" "', array_keys($all))));
            }
            throw new RuntimeException(sprintf('No arguments expected, got "%s".', $token));
        }
    }
    private function addShortOption($shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            throw new RuntimeException(sprintf('The "-%s" option does not exist.', $shortcut));
        }
        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }
    private function addLongOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            throw new RuntimeException(sprintf('The "--%s" option does not exist.', $name));
        }
        $option = $this->definition->getOption($name);
        if (null !== $value && !$option->acceptValue()) {
            throw new RuntimeException(sprintf('The "--%s" option does not accept a value.', $name));
        }
        if (\in_array($value, array('', null), true) && $option->acceptValue() && \count($this->parsed)) {
            $next = array_shift($this->parsed);
            if (isset($next[0]) && '-' !== $next[0] || \in_array($next, array('', null), true)) {
                $value = $next;
            } else {
                array_unshift($this->parsed, $next);
            }
        }
        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new RuntimeException(sprintf('The "--%s" option requires a value.', $name));
            }
            if (!$option->isArray() && !$option->isValueOptional()) {
                $value = true;
            }
        }
        if ($option->isArray()) {
            $this->options[$name][] = $value;
        } else {
            $this->options[$name] = $value;
        }
    }
    public function getFirstArgument()
    {
        foreach ($this->tokens as $token) {
            if ($token && '-' === $token[0]) {
                continue;
            }
            return $token;
        }
    }
    public function hasParameterOption($values, $onlyParams = false)
    {
        $values = (array) $values;
        foreach ($this->tokens as $token) {
            if ($onlyParams && '--' === $token) {
                return false;
            }
            foreach ($values as $value) {
                $leading = 0 === strpos($value, '--') ? $value . '=' : $value;
                if ($token === $value || '' !== $leading && 0 === strpos($token, $leading)) {
                    return true;
                }
            }
        }
        return false;
    }
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        $values = (array) $values;
        $tokens = $this->tokens;
        while (0 < \count($tokens)) {
            $token = array_shift($tokens);
            if ($onlyParams && '--' === $token) {
                return $default;
            }
            foreach ($values as $value) {
                if ($token === $value) {
                    return array_shift($tokens);
                }
                $leading = 0 === strpos($value, '--') ? $value . '=' : $value;
                if ('' !== $leading && 0 === strpos($token, $leading)) {
                    return substr($token, \strlen($leading));
                }
            }
        }
        return $default;
    }
    public function __toString()
    {
        $tokens = array_map(function ($token) {
            if (preg_match('{^(-[^=]+=)(.+)}', $token, $match)) {
                return $match[1] . $this->escapeToken($match[2]);
            }
            if ($token && '-' !== $token[0]) {
                return $this->escapeToken($token);
            }
            return $token;
        }, $this->tokens);
        return implode(' ', $tokens);
    }
}
}

namespace Symfony\Component\Console\Output {
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
interface OutputInterface
{
    const VERBOSITY_QUIET = 16;
    const VERBOSITY_NORMAL = 32;
    const VERBOSITY_VERBOSE = 64;
    const VERBOSITY_VERY_VERBOSE = 128;
    const VERBOSITY_DEBUG = 256;
    const OUTPUT_NORMAL = 1;
    const OUTPUT_RAW = 2;
    const OUTPUT_PLAIN = 4;
    public function write($messages, $newline = false, $options = 0);
    public function writeln($messages, $options = 0);
    public function setVerbosity($level);
    public function getVerbosity();
    public function isQuiet();
    public function isVerbose();
    public function isVeryVerbose();
    public function isDebug();
    public function setDecorated($decorated);
    public function isDecorated();
    public function setFormatter(OutputFormatterInterface $formatter);
    public function getFormatter();
}
}

namespace Symfony\Component\Console\Output {
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
abstract class Output implements OutputInterface
{
    private $verbosity;
    private $formatter;
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = false, OutputFormatterInterface $formatter = null)
    {
        $this->verbosity = null === $verbosity ? self::VERBOSITY_NORMAL : $verbosity;
        $this->formatter = $formatter ?: new OutputFormatter();
        $this->formatter->setDecorated($decorated);
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
    public function getFormatter()
    {
        return $this->formatter;
    }
    public function setDecorated($decorated)
    {
        $this->formatter->setDecorated($decorated);
    }
    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }
    public function setVerbosity($level)
    {
        $this->verbosity = (int) $level;
    }
    public function getVerbosity()
    {
        return $this->verbosity;
    }
    public function isQuiet()
    {
        return self::VERBOSITY_QUIET === $this->verbosity;
    }
    public function isVerbose()
    {
        return self::VERBOSITY_VERBOSE <= $this->verbosity;
    }
    public function isVeryVerbose()
    {
        return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
    }
    public function isDebug()
    {
        return self::VERBOSITY_DEBUG <= $this->verbosity;
    }
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        $this->write($messages, true, $options);
    }
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;
        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;
        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;
        if ($verbosity > $this->getVerbosity()) {
            return;
        }
        foreach ($messages as $message) {
            switch ($type) {
                case OutputInterface::OUTPUT_NORMAL:
                    $message = $this->formatter->format($message);
                    break;
                case OutputInterface::OUTPUT_RAW:
                    break;
                case OutputInterface::OUTPUT_PLAIN:
                    $message = strip_tags($this->formatter->format($message));
                    break;
            }
            $this->doWrite($message, $newline);
        }
    }
    protected abstract function doWrite($message, $newline);
}
}

namespace Symfony\Component\Console\Output {
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
class StreamOutput extends Output
{
    private $stream;
    public function __construct($stream, $verbosity = self::VERBOSITY_NORMAL, $decorated = null, OutputFormatterInterface $formatter = null)
    {
        if (!\is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
        }
        $this->stream = $stream;
        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }
        parent::__construct($verbosity, $decorated, $formatter);
    }
    public function getStream()
    {
        return $this->stream;
    }
    protected function doWrite($message, $newline)
    {
        if ($newline) {
            $message .= PHP_EOL;
        }
        if (false === @fwrite($this->stream, $message)) {
            throw new RuntimeException('Unable to write output.');
        }
        fflush($this->stream);
    }
    protected function hasColorSupport()
    {
        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            return \function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support($this->stream) || false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
        }
        if (\function_exists('stream_isatty')) {
            return @stream_isatty($this->stream);
        }
        if (\function_exists('posix_isatty')) {
            return @posix_isatty($this->stream);
        }
        $stat = @fstat($this->stream);
        return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
    }
}
}

namespace Symfony\Component\Console\Output {
interface ConsoleOutputInterface extends OutputInterface
{
    public function getErrorOutput();
    public function setErrorOutput(OutputInterface $error);
}
}

namespace Symfony\Component\Console\Output {
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    private $stderr;
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($this->openOutputStream(), $verbosity, $decorated, $formatter);
        $actualDecorated = $this->isDecorated();
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());
        if (null === $decorated) {
            $this->setDecorated($actualDecorated && $this->stderr->isDecorated());
        }
    }
    public function setDecorated($decorated)
    {
        parent::setDecorated($decorated);
        $this->stderr->setDecorated($decorated);
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->stderr->setFormatter($formatter);
    }
    public function setVerbosity($level)
    {
        parent::setVerbosity($level);
        $this->stderr->setVerbosity($level);
    }
    public function getErrorOutput()
    {
        return $this->stderr;
    }
    public function setErrorOutput(OutputInterface $error)
    {
        $this->stderr = $error;
    }
    protected function hasStdoutSupport()
    {
        return false === $this->isRunningOS400();
    }
    protected function hasStderrSupport()
    {
        return false === $this->isRunningOS400();
    }
    private function isRunningOS400()
    {
        $checks = array(\function_exists('php_uname') ? php_uname('s') : '', getenv('OSTYPE'), PHP_OS);
        return false !== stripos(implode(';', $checks), 'OS400');
    }
    private function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return fopen('php://output', 'w');
        }
        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
    }
    private function openErrorStream()
    {
        return fopen($this->hasStderrSupport() ? 'php://stderr' : 'php://output', 'w');
    }
}
}

namespace Symfony\Component\Console\Formatter {
interface OutputFormatterInterface
{
    public function setDecorated($decorated);
    public function isDecorated();
    public function setStyle($name, OutputFormatterStyleInterface $style);
    public function hasStyle($name);
    public function getStyle($name);
    public function format($message);
}
}

namespace Symfony\Component\Console\Formatter {
use Symfony\Component\Console\Exception\InvalidArgumentException;
class OutputFormatter implements OutputFormatterInterface
{
    private $decorated;
    private $styles = array();
    private $styleStack;
    public static function escape($text)
    {
        $text = preg_replace('/([^\\\\]?)</', '$1\\<', $text);
        return self::escapeTrailingBackslash($text);
    }
    public static function escapeTrailingBackslash($text)
    {
        if ('\\' === substr($text, -1)) {
            $len = \strlen($text);
            $text = rtrim($text, '\\');
            $text = str_replace("\0", '', $text);
            $text .= str_repeat("\0", $len - \strlen($text));
        }
        return $text;
    }
    public function __construct($decorated = false, array $styles = array())
    {
        $this->decorated = (bool) $decorated;
        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('info', new OutputFormatterStyle('green'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
        $this->styleStack = new OutputFormatterStyleStack();
    }
    public function setDecorated($decorated)
    {
        $this->decorated = (bool) $decorated;
    }
    public function isDecorated()
    {
        return $this->decorated;
    }
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        $this->styles[strtolower($name)] = $style;
    }
    public function hasStyle($name)
    {
        return isset($this->styles[strtolower($name)]);
    }
    public function getStyle($name)
    {
        if (!$this->hasStyle($name)) {
            throw new InvalidArgumentException(sprintf('Undefined style: %s', $name));
        }
        return $this->styles[strtolower($name)];
    }
    public function format($message)
    {
        $message = (string) $message;
        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][a-z0-9,_=;-]*+';
        preg_match_all("#<(({$tagRegex}) | /({$tagRegex})?)>#ix", $message, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];
            if (0 != $pos && '\\' == $message[$pos - 1]) {
                continue;
            }
            $output .= $this->applyCurrentStyle(substr($message, $offset, $pos - $offset));
            $offset = $pos + \strlen($text);
            if ($open = '/' != $text[1]) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = isset($matches[3][$i][0]) ? $matches[3][$i][0] : '';
            }
            if (!$open && !$tag) {
                $this->styleStack->pop();
            } elseif (false === ($style = $this->createStyleFromString($tag))) {
                $output .= $this->applyCurrentStyle($text);
            } elseif ($open) {
                $this->styleStack->push($style);
            } else {
                $this->styleStack->pop($style);
            }
        }
        $output .= $this->applyCurrentStyle(substr($message, $offset));
        if (false !== strpos($output, "\0")) {
            return strtr($output, array("\0" => '\\', '\\<' => '<'));
        }
        return str_replace('\\<', '<', $output);
    }
    public function getStyleStack()
    {
        return $this->styleStack;
    }
    private function createStyleFromString($string)
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }
        if (!preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, PREG_SET_ORDER)) {
            return false;
        }
        $style = new OutputFormatterStyle();
        foreach ($matches as $match) {
            array_shift($match);
            $match[0] = strtolower($match[0]);
            if ('fg' == $match[0]) {
                $style->setForeground(strtolower($match[1]));
            } elseif ('bg' == $match[0]) {
                $style->setBackground(strtolower($match[1]));
            } elseif ('options' === $match[0]) {
                preg_match_all('([^,;]+)', strtolower($match[1]), $options);
                $options = array_shift($options);
                foreach ($options as $option) {
                    try {
                        $style->setOption($option);
                    } catch (\InvalidArgumentException $e) {
                        @trigger_error(sprintf('Unknown style options are deprecated since Symfony 3.2 and will be removed in 4.0. Exception "%s".', $e->getMessage()), E_USER_DEPRECATED);
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
        return $style;
    }
    private function applyCurrentStyle($text)
    {
        return $this->isDecorated() && \strlen($text) > 0 ? $this->styleStack->getCurrent()->apply($text) : $text;
    }
}
}

namespace Symfony\Component\Console\Formatter {
interface OutputFormatterStyleInterface
{
    public function setForeground($color = null);
    public function setBackground($color = null);
    public function setOption($option);
    public function unsetOption($option);
    public function setOptions(array $options);
    public function apply($text);
}
}

namespace Symfony\Component\Console\Formatter {
use Symfony\Component\Console\Exception\InvalidArgumentException;
class OutputFormatterStyle implements OutputFormatterStyleInterface
{
    private static $availableForegroundColors = array('black' => array('set' => 30, 'unset' => 39), 'red' => array('set' => 31, 'unset' => 39), 'green' => array('set' => 32, 'unset' => 39), 'yellow' => array('set' => 33, 'unset' => 39), 'blue' => array('set' => 34, 'unset' => 39), 'magenta' => array('set' => 35, 'unset' => 39), 'cyan' => array('set' => 36, 'unset' => 39), 'white' => array('set' => 37, 'unset' => 39), 'default' => array('set' => 39, 'unset' => 39));
    private static $availableBackgroundColors = array('black' => array('set' => 40, 'unset' => 49), 'red' => array('set' => 41, 'unset' => 49), 'green' => array('set' => 42, 'unset' => 49), 'yellow' => array('set' => 43, 'unset' => 49), 'blue' => array('set' => 44, 'unset' => 49), 'magenta' => array('set' => 45, 'unset' => 49), 'cyan' => array('set' => 46, 'unset' => 49), 'white' => array('set' => 47, 'unset' => 49), 'default' => array('set' => 49, 'unset' => 49));
    private static $availableOptions = array('bold' => array('set' => 1, 'unset' => 22), 'underscore' => array('set' => 4, 'unset' => 24), 'blink' => array('set' => 5, 'unset' => 25), 'reverse' => array('set' => 7, 'unset' => 27), 'conceal' => array('set' => 8, 'unset' => 28));
    private $foreground;
    private $background;
    private $options = array();
    public function __construct($foreground = null, $background = null, array $options = array())
    {
        if (null !== $foreground) {
            $this->setForeground($foreground);
        }
        if (null !== $background) {
            $this->setBackground($background);
        }
        if (\count($options)) {
            $this->setOptions($options);
        }
    }
    public function setForeground($color = null)
    {
        if (null === $color) {
            $this->foreground = null;
            return;
        }
        if (!isset(static::$availableForegroundColors[$color])) {
            throw new InvalidArgumentException(sprintf('Invalid foreground color specified: "%s". Expected one of (%s)', $color, implode(', ', array_keys(static::$availableForegroundColors))));
        }
        $this->foreground = static::$availableForegroundColors[$color];
    }
    public function setBackground($color = null)
    {
        if (null === $color) {
            $this->background = null;
            return;
        }
        if (!isset(static::$availableBackgroundColors[$color])) {
            throw new InvalidArgumentException(sprintf('Invalid background color specified: "%s". Expected one of (%s)', $color, implode(', ', array_keys(static::$availableBackgroundColors))));
        }
        $this->background = static::$availableBackgroundColors[$color];
    }
    public function setOption($option)
    {
        if (!isset(static::$availableOptions[$option])) {
            throw new InvalidArgumentException(sprintf('Invalid option specified: "%s". Expected one of (%s)', $option, implode(', ', array_keys(static::$availableOptions))));
        }
        if (!\in_array(static::$availableOptions[$option], $this->options)) {
            $this->options[] = static::$availableOptions[$option];
        }
    }
    public function unsetOption($option)
    {
        if (!isset(static::$availableOptions[$option])) {
            throw new InvalidArgumentException(sprintf('Invalid option specified: "%s". Expected one of (%s)', $option, implode(', ', array_keys(static::$availableOptions))));
        }
        $pos = array_search(static::$availableOptions[$option], $this->options);
        if (false !== $pos) {
            unset($this->options[$pos]);
        }
    }
    public function setOptions(array $options)
    {
        $this->options = array();
        foreach ($options as $option) {
            $this->setOption($option);
        }
    }
    public function apply($text)
    {
        $setCodes = array();
        $unsetCodes = array();
        if (null !== $this->foreground) {
            $setCodes[] = $this->foreground['set'];
            $unsetCodes[] = $this->foreground['unset'];
        }
        if (null !== $this->background) {
            $setCodes[] = $this->background['set'];
            $unsetCodes[] = $this->background['unset'];
        }
        if (\count($this->options)) {
            foreach ($this->options as $option) {
                $setCodes[] = $option['set'];
                $unsetCodes[] = $option['unset'];
            }
        }
        if (0 === \count($setCodes)) {
            return $text;
        }
        return sprintf("\33[%sm%s\33[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
    }
}
}

namespace Symfony\Component\Console\Formatter {
use Symfony\Component\Console\Exception\InvalidArgumentException;
class OutputFormatterStyleStack
{
    private $styles;
    private $emptyStyle;
    public function __construct(OutputFormatterStyleInterface $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?: new OutputFormatterStyle();
        $this->reset();
    }
    public function reset()
    {
        $this->styles = array();
    }
    public function push(OutputFormatterStyleInterface $style)
    {
        $this->styles[] = $style;
    }
    public function pop(OutputFormatterStyleInterface $style = null)
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        if (null === $style) {
            return array_pop($this->styles);
        }
        foreach (array_reverse($this->styles, true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);
                return $stackedStyle;
            }
        }
        throw new InvalidArgumentException('Incorrectly nested style tag found.');
    }
    public function getCurrent()
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        return $this->styles[\count($this->styles) - 1];
    }
    public function setEmptyStyle(OutputFormatterStyleInterface $emptyStyle)
    {
        $this->emptyStyle = $emptyStyle;
        return $this;
    }
    public function getEmptyStyle()
    {
        return $this->emptyStyle;
    }
}
}

namespace Symfony\Component\Console\Helper {
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Descriptor\JsonDescriptor;
use Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
class DescriptorHelper extends Helper
{
    private $descriptors = array();
    public function __construct()
    {
        $this->register('txt', new TextDescriptor())->register('xml', new XmlDescriptor())->register('json', new JsonDescriptor())->register('md', new MarkdownDescriptor());
    }
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $options = array_merge(array('raw_text' => false, 'format' => 'txt'), $options);
        if (!isset($this->descriptors[$options['format']])) {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $options['format']));
        }
        $descriptor = $this->descriptors[$options['format']];
        $descriptor->describe($output, $object, $options);
    }
    public function register($format, DescriptorInterface $descriptor)
    {
        $this->descriptors[$format] = $descriptor;
        return $this;
    }
    public function getName()
    {
        return 'descriptor';
    }
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
class ApplicationDescription
{
    const GLOBAL_NAMESPACE = '_global';
    private $application;
    private $namespace;
    private $showHidden;
    private $namespaces;
    private $commands;
    private $aliases;
    public function __construct(Application $application, $namespace = null, $showHidden = false)
    {
        $this->application = $application;
        $this->namespace = $namespace;
        $this->showHidden = $showHidden;
    }
    public function getNamespaces()
    {
        if (null === $this->namespaces) {
            $this->inspectApplication();
        }
        return $this->namespaces;
    }
    public function getCommands()
    {
        if (null === $this->commands) {
            $this->inspectApplication();
        }
        return $this->commands;
    }
    public function getCommand($name)
    {
        if (!isset($this->commands[$name]) && !isset($this->aliases[$name])) {
            throw new CommandNotFoundException(sprintf('Command %s does not exist.', $name));
        }
        return isset($this->commands[$name]) ? $this->commands[$name] : $this->aliases[$name];
    }
    private function inspectApplication()
    {
        $this->commands = array();
        $this->namespaces = array();
        $all = $this->application->all($this->namespace ? $this->application->findNamespace($this->namespace) : null);
        foreach ($this->sortCommands($all) as $namespace => $commands) {
            $names = array();
            foreach ($commands as $name => $command) {
                if (!$command->getName() || !$this->showHidden && $command->isHidden()) {
                    continue;
                }
                if ($command->getName() === $name) {
                    $this->commands[$name] = $command;
                } else {
                    $this->aliases[$name] = $command;
                }
                $names[] = $name;
            }
            $this->namespaces[$namespace] = array('id' => $namespace, 'commands' => $names);
        }
    }
    private function sortCommands(array $commands)
    {
        $namespacedCommands = array();
        $globalCommands = array();
        foreach ($commands as $name => $command) {
            $key = $this->application->extractNamespace($name, 1);
            if (!$key) {
                $globalCommands['_global'][$name] = $command;
            } else {
                $namespacedCommands[$key][$name] = $command;
            }
        }
        ksort($namespacedCommands);
        $namespacedCommands = array_merge($globalCommands, $namespacedCommands);
        foreach ($namespacedCommands as &$commandsSet) {
            ksort($commandsSet);
        }
        unset($commandsSet);
        return $namespacedCommands;
    }
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Output\OutputInterface;
interface DescriptorInterface
{
    public function describe(OutputInterface $output, $object, array $options = array());
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
abstract class Descriptor implements DescriptorInterface
{
    protected $output;
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $this->output = $output;
        switch (true) {
            case $object instanceof InputArgument:
                $this->describeInputArgument($object, $options);
                break;
            case $object instanceof InputOption:
                $this->describeInputOption($object, $options);
                break;
            case $object instanceof InputDefinition:
                $this->describeInputDefinition($object, $options);
                break;
            case $object instanceof Command:
                $this->describeCommand($object, $options);
                break;
            case $object instanceof Application:
                $this->describeApplication($object, $options);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Object of type "%s" is not describable.', \get_class($object)));
        }
    }
    protected function write($content, $decorated = false)
    {
        $this->output->write($content, false, $decorated ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }
    protected abstract function describeInputArgument(InputArgument $argument, array $options = array());
    protected abstract function describeInputOption(InputOption $option, array $options = array());
    protected abstract function describeInputDefinition(InputDefinition $definition, array $options = array());
    protected abstract function describeCommand(Command $command, array $options = array());
    protected abstract function describeApplication(Application $application, array $options = array());
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
class TextDescriptor extends Descriptor
{
    protected function describeInputArgument(InputArgument $argument, array $options = array())
    {
        if (null !== $argument->getDefault() && (!\is_array($argument->getDefault()) || \count($argument->getDefault()))) {
            $default = sprintf('<comment> [default: %s]</comment>', $this->formatDefaultValue($argument->getDefault()));
        } else {
            $default = '';
        }
        $totalWidth = isset($options['total_width']) ? $options['total_width'] : Helper::strlen($argument->getName());
        $spacingWidth = $totalWidth - \strlen($argument->getName());
        $this->writeText(sprintf('  <info>%s</info>  %s%s%s', $argument->getName(), str_repeat(' ', $spacingWidth), preg_replace('/\\s*[\\r\\n]\\s*/', "\n" . str_repeat(' ', $totalWidth + 4), $argument->getDescription()), $default), $options);
    }
    protected function describeInputOption(InputOption $option, array $options = array())
    {
        if ($option->acceptValue() && null !== $option->getDefault() && (!\is_array($option->getDefault()) || \count($option->getDefault()))) {
            $default = sprintf('<comment> [default: %s]</comment>', $this->formatDefaultValue($option->getDefault()));
        } else {
            $default = '';
        }
        $value = '';
        if ($option->acceptValue()) {
            $value = '=' . strtoupper($option->getName());
            if ($option->isValueOptional()) {
                $value = '[' . $value . ']';
            }
        }
        $totalWidth = isset($options['total_width']) ? $options['total_width'] : $this->calculateTotalWidthForOptions(array($option));
        $synopsis = sprintf('%s%s', $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ', sprintf('--%s%s', $option->getName(), $value));
        $spacingWidth = $totalWidth - Helper::strlen($synopsis);
        $this->writeText(sprintf('  <info>%s</info>  %s%s%s%s', $synopsis, str_repeat(' ', $spacingWidth), preg_replace('/\\s*[\\r\\n]\\s*/', "\n" . str_repeat(' ', $totalWidth + 4), $option->getDescription()), $default, $option->isArray() ? '<comment> (multiple values allowed)</comment>' : ''), $options);
    }
    protected function describeInputDefinition(InputDefinition $definition, array $options = array())
    {
        $totalWidth = $this->calculateTotalWidthForOptions($definition->getOptions());
        foreach ($definition->getArguments() as $argument) {
            $totalWidth = max($totalWidth, Helper::strlen($argument->getName()));
        }
        if ($definition->getArguments()) {
            $this->writeText('<comment>Arguments:</comment>', $options);
            $this->writeText("\n");
            foreach ($definition->getArguments() as $argument) {
                $this->describeInputArgument($argument, array_merge($options, array('total_width' => $totalWidth)));
                $this->writeText("\n");
            }
        }
        if ($definition->getArguments() && $definition->getOptions()) {
            $this->writeText("\n");
        }
        if ($definition->getOptions()) {
            $laterOptions = array();
            $this->writeText('<comment>Options:</comment>', $options);
            foreach ($definition->getOptions() as $option) {
                if (\strlen($option->getShortcut()) > 1) {
                    $laterOptions[] = $option;
                    continue;
                }
                $this->writeText("\n");
                $this->describeInputOption($option, array_merge($options, array('total_width' => $totalWidth)));
            }
            foreach ($laterOptions as $option) {
                $this->writeText("\n");
                $this->describeInputOption($option, array_merge($options, array('total_width' => $totalWidth)));
            }
        }
    }
    protected function describeCommand(Command $command, array $options = array())
    {
        $command->getSynopsis(true);
        $command->getSynopsis(false);
        $command->mergeApplicationDefinition(false);
        $this->writeText('<comment>Usage:</comment>', $options);
        foreach (array_merge(array($command->getSynopsis(true)), $command->getAliases(), $command->getUsages()) as $usage) {
            $this->writeText("\n");
            $this->writeText('  ' . OutputFormatter::escape($usage), $options);
        }
        $this->writeText("\n");
        $definition = $command->getNativeDefinition();
        if ($definition->getOptions() || $definition->getArguments()) {
            $this->writeText("\n");
            $this->describeInputDefinition($definition, $options);
            $this->writeText("\n");
        }
        if ($help = $command->getProcessedHelp()) {
            $this->writeText("\n");
            $this->writeText('<comment>Help:</comment>', $options);
            $this->writeText("\n");
            $this->writeText('  ' . str_replace("\n", "\n  ", $help), $options);
            $this->writeText("\n");
        }
    }
    protected function describeApplication(Application $application, array $options = array())
    {
        $describedNamespace = isset($options['namespace']) ? $options['namespace'] : null;
        $description = new ApplicationDescription($application, $describedNamespace);
        if (isset($options['raw_text']) && $options['raw_text']) {
            $width = $this->getColumnWidth($description->getCommands());
            foreach ($description->getCommands() as $command) {
                $this->writeText(sprintf("%-{$width}s %s", $command->getName(), $command->getDescription()), $options);
                $this->writeText("\n");
            }
        } else {
            if ('' != ($help = $application->getHelp())) {
                $this->writeText("{$help}\n\n", $options);
            }
            $this->writeText("<comment>Usage:</comment>\n", $options);
            $this->writeText("  command [options] [arguments]\n\n", $options);
            $this->describeInputDefinition(new InputDefinition($application->getDefinition()->getOptions()), $options);
            $this->writeText("\n");
            $this->writeText("\n");
            $commands = $description->getCommands();
            $namespaces = $description->getNamespaces();
            if ($describedNamespace && $namespaces) {
                $describedNamespaceInfo = reset($namespaces);
                foreach ($describedNamespaceInfo['commands'] as $name) {
                    $commands[$name] = $description->getCommand($name);
                }
            }
            $width = $this->getColumnWidth(\call_user_func_array('array_merge', array_map(function ($namespace) use($commands) {
                return array_intersect($namespace['commands'], array_keys($commands));
            }, $namespaces)));
            if ($describedNamespace) {
                $this->writeText(sprintf('<comment>Available commands for the "%s" namespace:</comment>', $describedNamespace), $options);
            } else {
                $this->writeText('<comment>Available commands:</comment>', $options);
            }
            foreach ($namespaces as $namespace) {
                $namespace['commands'] = array_filter($namespace['commands'], function ($name) use($commands) {
                    return isset($commands[$name]);
                });
                if (!$namespace['commands']) {
                    continue;
                }
                if (!$describedNamespace && ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id']) {
                    $this->writeText("\n");
                    $this->writeText(' <comment>' . $namespace['id'] . '</comment>', $options);
                }
                foreach ($namespace['commands'] as $name) {
                    $this->writeText("\n");
                    $spacingWidth = $width - Helper::strlen($name);
                    $command = $commands[$name];
                    $commandAliases = $name === $command->getName() ? $this->getCommandAliasesText($command) : '';
                    $this->writeText(sprintf('  <info>%s</info>%s%s', $name, str_repeat(' ', $spacingWidth), $commandAliases . $command->getDescription()), $options);
                }
            }
            $this->writeText("\n");
        }
    }
    private function writeText($content, array $options = array())
    {
        $this->write(isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content, isset($options['raw_output']) ? !$options['raw_output'] : true);
    }
    private function getCommandAliasesText(Command $command)
    {
        $text = '';
        $aliases = $command->getAliases();
        if ($aliases) {
            $text = '[' . implode('|', $aliases) . '] ';
        }
        return $text;
    }
    private function formatDefaultValue($default)
    {
        if (INF === $default) {
            return 'INF';
        }
        if (\is_string($default)) {
            $default = OutputFormatter::escape($default);
        } elseif (\is_array($default)) {
            foreach ($default as $key => $value) {
                if (\is_string($value)) {
                    $default[$key] = OutputFormatter::escape($value);
                }
            }
        }
        return str_replace('\\\\', '\\', json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    private function getColumnWidth(array $commands)
    {
        $widths = array();
        foreach ($commands as $command) {
            if ($command instanceof Command) {
                $widths[] = Helper::strlen($command->getName());
                foreach ($command->getAliases() as $alias) {
                    $widths[] = Helper::strlen($alias);
                }
            } else {
                $widths[] = Helper::strlen($command);
            }
        }
        return $widths ? max($widths) + 2 : 0;
    }
    private function calculateTotalWidthForOptions(array $options)
    {
        $totalWidth = 0;
        foreach ($options as $option) {
            $nameLength = 1 + max(Helper::strlen($option->getShortcut()), 1) + 4 + Helper::strlen($option->getName());
            if ($option->acceptValue()) {
                $valueLength = 1 + Helper::strlen($option->getName());
                $valueLength += $option->isValueOptional() ? 2 : 0;
                $nameLength += $valueLength;
            }
            $totalWidth = max($totalWidth, $nameLength);
        }
        return $totalWidth;
    }
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
class JsonDescriptor extends Descriptor
{
    protected function describeInputArgument(InputArgument $argument, array $options = array())
    {
        $this->writeData($this->getInputArgumentData($argument), $options);
    }
    protected function describeInputOption(InputOption $option, array $options = array())
    {
        $this->writeData($this->getInputOptionData($option), $options);
    }
    protected function describeInputDefinition(InputDefinition $definition, array $options = array())
    {
        $this->writeData($this->getInputDefinitionData($definition), $options);
    }
    protected function describeCommand(Command $command, array $options = array())
    {
        $this->writeData($this->getCommandData($command), $options);
    }
    protected function describeApplication(Application $application, array $options = array())
    {
        $describedNamespace = isset($options['namespace']) ? $options['namespace'] : null;
        $description = new ApplicationDescription($application, $describedNamespace, true);
        $commands = array();
        foreach ($description->getCommands() as $command) {
            $commands[] = $this->getCommandData($command);
        }
        $data = array();
        if ('UNKNOWN' !== $application->getName()) {
            $data['application']['name'] = $application->getName();
            if ('UNKNOWN' !== $application->getVersion()) {
                $data['application']['version'] = $application->getVersion();
            }
        }
        $data['commands'] = $commands;
        if ($describedNamespace) {
            $data['namespace'] = $describedNamespace;
        } else {
            $data['namespaces'] = array_values($description->getNamespaces());
        }
        $this->writeData($data, $options);
    }
    private function writeData(array $data, array $options)
    {
        $this->write(json_encode($data, isset($options['json_encoding']) ? $options['json_encoding'] : 0));
    }
    private function getInputArgumentData(InputArgument $argument)
    {
        return array('name' => $argument->getName(), 'is_required' => $argument->isRequired(), 'is_array' => $argument->isArray(), 'description' => preg_replace('/\\s*[\\r\\n]\\s*/', ' ', $argument->getDescription()), 'default' => INF === $argument->getDefault() ? 'INF' : $argument->getDefault());
    }
    private function getInputOptionData(InputOption $option)
    {
        return array('name' => '--' . $option->getName(), 'shortcut' => $option->getShortcut() ? '-' . str_replace('|', '|-', $option->getShortcut()) : '', 'accept_value' => $option->acceptValue(), 'is_value_required' => $option->isValueRequired(), 'is_multiple' => $option->isArray(), 'description' => preg_replace('/\\s*[\\r\\n]\\s*/', ' ', $option->getDescription()), 'default' => INF === $option->getDefault() ? 'INF' : $option->getDefault());
    }
    private function getInputDefinitionData(InputDefinition $definition)
    {
        $inputArguments = array();
        foreach ($definition->getArguments() as $name => $argument) {
            $inputArguments[$name] = $this->getInputArgumentData($argument);
        }
        $inputOptions = array();
        foreach ($definition->getOptions() as $name => $option) {
            $inputOptions[$name] = $this->getInputOptionData($option);
        }
        return array('arguments' => $inputArguments, 'options' => $inputOptions);
    }
    private function getCommandData(Command $command)
    {
        $command->getSynopsis();
        $command->mergeApplicationDefinition(false);
        return array('name' => $command->getName(), 'usage' => array_merge(array($command->getSynopsis()), $command->getUsages(), $command->getAliases()), 'description' => $command->getDescription(), 'help' => $command->getProcessedHelp(), 'definition' => $this->getInputDefinitionData($command->getNativeDefinition()), 'hidden' => $command->isHidden());
    }
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
class XmlDescriptor extends Descriptor
{
    public function getInputDefinitionDocument(InputDefinition $definition)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($definitionXML = $dom->createElement('definition'));
        $definitionXML->appendChild($argumentsXML = $dom->createElement('arguments'));
        foreach ($definition->getArguments() as $argument) {
            $this->appendDocument($argumentsXML, $this->getInputArgumentDocument($argument));
        }
        $definitionXML->appendChild($optionsXML = $dom->createElement('options'));
        foreach ($definition->getOptions() as $option) {
            $this->appendDocument($optionsXML, $this->getInputOptionDocument($option));
        }
        return $dom;
    }
    public function getCommandDocument(Command $command)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($commandXML = $dom->createElement('command'));
        $command->getSynopsis();
        $command->mergeApplicationDefinition(false);
        $commandXML->setAttribute('id', $command->getName());
        $commandXML->setAttribute('name', $command->getName());
        $commandXML->setAttribute('hidden', $command->isHidden() ? 1 : 0);
        $commandXML->appendChild($usagesXML = $dom->createElement('usages'));
        foreach (array_merge(array($command->getSynopsis()), $command->getAliases(), $command->getUsages()) as $usage) {
            $usagesXML->appendChild($dom->createElement('usage', $usage));
        }
        $commandXML->appendChild($descriptionXML = $dom->createElement('description'));
        $descriptionXML->appendChild($dom->createTextNode(str_replace("\n", "\n ", $command->getDescription())));
        $commandXML->appendChild($helpXML = $dom->createElement('help'));
        $helpXML->appendChild($dom->createTextNode(str_replace("\n", "\n ", $command->getProcessedHelp())));
        $definitionXML = $this->getInputDefinitionDocument($command->getNativeDefinition());
        $this->appendDocument($commandXML, $definitionXML->getElementsByTagName('definition')->item(0));
        return $dom;
    }
    public function getApplicationDocument(Application $application, $namespace = null)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($rootXml = $dom->createElement('symfony'));
        if ('UNKNOWN' !== $application->getName()) {
            $rootXml->setAttribute('name', $application->getName());
            if ('UNKNOWN' !== $application->getVersion()) {
                $rootXml->setAttribute('version', $application->getVersion());
            }
        }
        $rootXml->appendChild($commandsXML = $dom->createElement('commands'));
        $description = new ApplicationDescription($application, $namespace, true);
        if ($namespace) {
            $commandsXML->setAttribute('namespace', $namespace);
        }
        foreach ($description->getCommands() as $command) {
            $this->appendDocument($commandsXML, $this->getCommandDocument($command));
        }
        if (!$namespace) {
            $rootXml->appendChild($namespacesXML = $dom->createElement('namespaces'));
            foreach ($description->getNamespaces() as $namespaceDescription) {
                $namespacesXML->appendChild($namespaceArrayXML = $dom->createElement('namespace'));
                $namespaceArrayXML->setAttribute('id', $namespaceDescription['id']);
                foreach ($namespaceDescription['commands'] as $name) {
                    $namespaceArrayXML->appendChild($commandXML = $dom->createElement('command'));
                    $commandXML->appendChild($dom->createTextNode($name));
                }
            }
        }
        return $dom;
    }
    protected function describeInputArgument(InputArgument $argument, array $options = array())
    {
        $this->writeDocument($this->getInputArgumentDocument($argument));
    }
    protected function describeInputOption(InputOption $option, array $options = array())
    {
        $this->writeDocument($this->getInputOptionDocument($option));
    }
    protected function describeInputDefinition(InputDefinition $definition, array $options = array())
    {
        $this->writeDocument($this->getInputDefinitionDocument($definition));
    }
    protected function describeCommand(Command $command, array $options = array())
    {
        $this->writeDocument($this->getCommandDocument($command));
    }
    protected function describeApplication(Application $application, array $options = array())
    {
        $this->writeDocument($this->getApplicationDocument($application, isset($options['namespace']) ? $options['namespace'] : null));
    }
    private function appendDocument(\DOMNode $parentNode, \DOMNode $importedParent)
    {
        foreach ($importedParent->childNodes as $childNode) {
            $parentNode->appendChild($parentNode->ownerDocument->importNode($childNode, true));
        }
    }
    private function writeDocument(\DOMDocument $dom)
    {
        $dom->formatOutput = true;
        $this->write($dom->saveXML());
    }
    private function getInputArgumentDocument(InputArgument $argument)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($objectXML = $dom->createElement('argument'));
        $objectXML->setAttribute('name', $argument->getName());
        $objectXML->setAttribute('is_required', $argument->isRequired() ? 1 : 0);
        $objectXML->setAttribute('is_array', $argument->isArray() ? 1 : 0);
        $objectXML->appendChild($descriptionXML = $dom->createElement('description'));
        $descriptionXML->appendChild($dom->createTextNode($argument->getDescription()));
        $objectXML->appendChild($defaultsXML = $dom->createElement('defaults'));
        $defaults = \is_array($argument->getDefault()) ? $argument->getDefault() : (\is_bool($argument->getDefault()) ? array(var_export($argument->getDefault(), true)) : ($argument->getDefault() ? array($argument->getDefault()) : array()));
        foreach ($defaults as $default) {
            $defaultsXML->appendChild($defaultXML = $dom->createElement('default'));
            $defaultXML->appendChild($dom->createTextNode($default));
        }
        return $dom;
    }
    private function getInputOptionDocument(InputOption $option)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($objectXML = $dom->createElement('option'));
        $objectXML->setAttribute('name', '--' . $option->getName());
        $pos = strpos($option->getShortcut(), '|');
        if (false !== $pos) {
            $objectXML->setAttribute('shortcut', '-' . substr($option->getShortcut(), 0, $pos));
            $objectXML->setAttribute('shortcuts', '-' . str_replace('|', '|-', $option->getShortcut()));
        } else {
            $objectXML->setAttribute('shortcut', $option->getShortcut() ? '-' . $option->getShortcut() : '');
        }
        $objectXML->setAttribute('accept_value', $option->acceptValue() ? 1 : 0);
        $objectXML->setAttribute('is_value_required', $option->isValueRequired() ? 1 : 0);
        $objectXML->setAttribute('is_multiple', $option->isArray() ? 1 : 0);
        $objectXML->appendChild($descriptionXML = $dom->createElement('description'));
        $descriptionXML->appendChild($dom->createTextNode($option->getDescription()));
        if ($option->acceptValue()) {
            $defaults = \is_array($option->getDefault()) ? $option->getDefault() : (\is_bool($option->getDefault()) ? array(var_export($option->getDefault(), true)) : ($option->getDefault() ? array($option->getDefault()) : array()));
            $objectXML->appendChild($defaultsXML = $dom->createElement('defaults'));
            if (!empty($defaults)) {
                foreach ($defaults as $default) {
                    $defaultsXML->appendChild($defaultXML = $dom->createElement('default'));
                    $defaultXML->appendChild($dom->createTextNode($default));
                }
            }
        }
        return $dom;
    }
}
}

namespace Symfony\Component\Console\Descriptor {
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class MarkdownDescriptor extends Descriptor
{
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $decorated = $output->isDecorated();
        $output->setDecorated(false);
        parent::describe($output, $object, $options);
        $output->setDecorated($decorated);
    }
    protected function write($content, $decorated = true)
    {
        parent::write($content, $decorated);
    }
    protected function describeInputArgument(InputArgument $argument, array $options = array())
    {
        $this->write('#### `' . ($argument->getName() ?: '<none>') . "`\n\n" . ($argument->getDescription() ? preg_replace('/\\s*[\\r\\n]\\s*/', "\n", $argument->getDescription()) . "\n\n" : '') . '* Is required: ' . ($argument->isRequired() ? 'yes' : 'no') . "\n" . '* Is array: ' . ($argument->isArray() ? 'yes' : 'no') . "\n" . '* Default: `' . str_replace("\n", '', var_export($argument->getDefault(), true)) . '`');
    }
    protected function describeInputOption(InputOption $option, array $options = array())
    {
        $name = '--' . $option->getName();
        if ($option->getShortcut()) {
            $name .= '|-' . str_replace('|', '|-', $option->getShortcut()) . '';
        }
        $this->write('#### `' . $name . '`' . "\n\n" . ($option->getDescription() ? preg_replace('/\\s*[\\r\\n]\\s*/', "\n", $option->getDescription()) . "\n\n" : '') . '* Accept value: ' . ($option->acceptValue() ? 'yes' : 'no') . "\n" . '* Is value required: ' . ($option->isValueRequired() ? 'yes' : 'no') . "\n" . '* Is multiple: ' . ($option->isArray() ? 'yes' : 'no') . "\n" . '* Default: `' . str_replace("\n", '', var_export($option->getDefault(), true)) . '`');
    }
    protected function describeInputDefinition(InputDefinition $definition, array $options = array())
    {
        if ($showArguments = \count($definition->getArguments()) > 0) {
            $this->write('### Arguments');
            foreach ($definition->getArguments() as $argument) {
                $this->write("\n\n");
                $this->write($this->describeInputArgument($argument));
            }
        }
        if (\count($definition->getOptions()) > 0) {
            if ($showArguments) {
                $this->write("\n\n");
            }
            $this->write('### Options');
            foreach ($definition->getOptions() as $option) {
                $this->write("\n\n");
                $this->write($this->describeInputOption($option));
            }
        }
    }
    protected function describeCommand(Command $command, array $options = array())
    {
        $command->getSynopsis();
        $command->mergeApplicationDefinition(false);
        $this->write('`' . $command->getName() . "`\n" . str_repeat('-', Helper::strlen($command->getName()) + 2) . "\n\n" . ($command->getDescription() ? $command->getDescription() . "\n\n" : '') . '### Usage' . "\n\n" . array_reduce(array_merge(array($command->getSynopsis()), $command->getAliases(), $command->getUsages()), function ($carry, $usage) {
            return $carry . '* `' . $usage . '`' . "\n";
        }));
        if ($help = $command->getProcessedHelp()) {
            $this->write("\n");
            $this->write($help);
        }
        if ($command->getNativeDefinition()) {
            $this->write("\n\n");
            $this->describeInputDefinition($command->getNativeDefinition());
        }
    }
    protected function describeApplication(Application $application, array $options = array())
    {
        $describedNamespace = isset($options['namespace']) ? $options['namespace'] : null;
        $description = new ApplicationDescription($application, $describedNamespace);
        $title = $this->getApplicationTitle($application);
        $this->write($title . "\n" . str_repeat('=', Helper::strlen($title)));
        foreach ($description->getNamespaces() as $namespace) {
            if (ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id']) {
                $this->write("\n\n");
                $this->write('**' . $namespace['id'] . ':**');
            }
            $this->write("\n\n");
            $this->write(implode("\n", array_map(function ($commandName) use($description) {
                return sprintf('* [`%s`](#%s)', $commandName, str_replace(':', '', $description->getCommand($commandName)->getName()));
            }, $namespace['commands'])));
        }
        foreach ($description->getCommands() as $command) {
            $this->write("\n\n");
            $this->write($this->describeCommand($command));
        }
    }
    private function getApplicationTitle(Application $application)
    {
        if ('UNKNOWN' !== $application->getName()) {
            if ('UNKNOWN' !== $application->getVersion()) {
                return sprintf('%s %s', $application->getName(), $application->getVersion());
            }
            return $application->getName();
        }
        return 'Console Tool';
    }
}
}

namespace Symfony\Component\Console\Exception {
interface ExceptionInterface
{
}
}

namespace Symfony\Component\Console\Exception {
class CommandNotFoundException extends \InvalidArgumentException implements ExceptionInterface
{
    private $alternatives;
    public function __construct($message, array $alternatives = array(), $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->alternatives = $alternatives;
    }
    public function getAlternatives()
    {
        return $this->alternatives;
    }
}
}

namespace Symfony\Component\Console\Exception {
class InvalidOptionException extends \InvalidArgumentException implements ExceptionInterface
{
}
}

namespace Symfony\Component\Console\Exception {
class LogicException extends \LogicException implements ExceptionInterface
{
}
}

namespace Symfony\Component\Console\Exception {
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
}

namespace Symfony\Component\Console\Exception {
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
}

namespace {
use Views\WebView;
use Controllers\InstallController;
use Models\StepModel;
use Commands\InstallCommand;
use Symfony\Component\Console\Application;
if (php_sapi_name() == "cli" && empty($globalForceWeb)) {
    $command = new InstallCommand();
    $application = new Application();
    $application->add($command);
    $application->setDefaultCommand($command->getName());
    $application->setAutoExit(false);
    $application->run();
} else {
    session_start();
    $model = new StepModel();
    $view = new WebView();
    $controller = new InstallController($view, $model);
    $controller->dispatchSingle();
}
}

