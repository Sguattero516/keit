<?php
namespace Traffic\Actions\Repository;

use Component\StreamActions\Repository\CustomStreamActionRepository;
use Core\Locale\LocaleService;
use Traffic\Actions\AbstractAction;
use Traffic\Actions\ActionError;
use Traffic\Actions\Predefined\BlankReferrer;
use Traffic\Actions\Predefined\Curl;
use Traffic\Actions\Predefined\DoNothing;
use Traffic\Actions\Predefined\DoubleMeta;
use Traffic\Actions\Predefined\FormSubmit;
use Traffic\Actions\Predefined\Frame;
use Traffic\Actions\Predefined\HttpRedirect;
use Traffic\Actions\Predefined\Iframe;
use Traffic\Actions\Predefined\Js;
use Traffic\Actions\Predefined\JsForIframe;
use Traffic\Actions\Predefined\JsForScript;
use Traffic\Actions\Predefined\LocalFile;
use Traffic\Actions\Predefined\Meta;
use Traffic\Actions\Predefined\Remote;
use Traffic\Actions\Predefined\ShowHtml;
use Traffic\Actions\Predefined\ShowText;
use Traffic\Actions\Predefined\Status404;
use Traffic\Actions\Predefined\SubId;
use Traffic\Actions\Predefined\ToCampaign;
use Traffic\Repository\AbstractBaseRepository;
use Traffic\Logging\Service\LoggerService;

class StreamActionRepository extends AbstractBaseRepository
{
    const BUILD_HTML = 'build_html';

    const DEFAULT_CUSTOM_PATH = '/application/redirects';

    private $_actions = [];

    private $_aliases = [];

    private $_exclude = ['example', 'build_html', 'sub_id'];

    public function __construct($customPath = null)
    {
        $this->_registerBuiltInActions();

        if (empty($customPath)) {
            $customPath = ROOT . self::DEFAULT_CUSTOM_PATH;
        }

        $this->loadCustomActions($customPath);
    }

    public function loadCustomActions($customPath)
    {
        $customActionArray = CustomStreamActionRepository::instance()->getCustomStreamActions($customPath, $this->_actions);
        foreach ($customActionArray as $name => $redirect) {
            $this->register($name, $redirect);
        }

        $this->_sort();

        if (empty($this->_actions['location'])) {
            $this->alias('location', 'http');
        }
    }

    private function _registerBuiltInActions()
    {
        $this->register('blank_referrer', new BlankReferrer());
        $this->register('show_html', new ShowHtml());
        $this->alias('build_html', 'show_html');
        $this->alias('return_html', 'show_html');

        $this->register('campaign', new ToCampaign());
        $this->alias('group', 'campaign');

        $this->register('curl', new Curl());
        $this->register('double_meta', new DoubleMeta());
        $this->register('show_text', new ShowText());
        $this->alias('echo', 'show_text');

        $this->register('formsubmit', new FormSubmit());
        $this->register('frame', new Frame());
        $this->register('iframe', new Iframe());

        $this->register('http', new HttpRedirect());
        $this->register('js', new Js());
        $this->register('js_for_iframe', new JsForIframe());
        $this->register('js_for_script', new JsForScript());
        $this->register('meta', new Meta());
        $this->register('remote', new Remote());
        $this->register('status404', new Status404());
        $this->register('sub_id', new SubId());
        $this->register('do_nothing', new DoNothing());
        $this->register('local_file', new LocalFile());
    }

    public function register($name, $obj)
    {
        if (!empty($this->_actions[$name])) {
            LoggerService::instance()->error("Redirect {$name} is already exists. Please remove or rename the file redirects/{$name}.php.");
            return;
        }
        $this->_actions[$name] = $obj;
    }

    public function alias($aliasName, $redirectName)
    {
        if (empty($this->_actions[$redirectName])) {
            throw new ActionError("Action {$redirectName} is not defined");
        }
        if (!empty($this->_actions[$aliasName])) {
            throw new ActionError("Action {$aliasName} is already defined");
        }
        $this->_aliases[] = $aliasName;
        $this->_actions[$aliasName] = $this->_actions[$redirectName];
    }

    public function isRedirect($name)
    {
        return (isset($this->_actions[$name]) && $this->_actions[$name]->getType() == AbstractAction::TYPE_REDIRECT);
    }

    public function getNewActionInstance($name)
    {
        if (empty($this->_actions[$name])) {
            throw new ActionError("Redirect '{$name}' is not defined");
        }
        return clone $this->_actions[$name];
    }

    public function getActions()
    {
        return $this->_actions;
    }

    public function getNames()
    {
        return array_keys($this->_actions);
    }

    public function getListAsOptions()
    {
        $options = array();

        foreach ($this->_actions as $key => $action) {
            if (!in_array($key, $this->_aliases) && !in_array($key, $this->_exclude)) {
                if (LocaleService::instance()->exists('stream_actions.action_descriptions.' . $key)) {
                    $description = LocaleService::t('stream_actions.action_descriptions.' . $key);
                } else {
                    $description = '';
                }

                $options[] = [
                    'key' => $key,
                    'name' => $action->getName(),
                    'field' => $action->getField(),
                    'type' => $action->getType(),
                    'description' => $description,
                ];
            }
        }

        return $options;
    }

    private function _sort()
    {
        $sortCmp = function($el1, $el2) {
            return ($el1->getWeight() > $el2->getWeight()) ? +1 : -1;
        };

        uasort($this->_actions, $sortCmp);
    }
}