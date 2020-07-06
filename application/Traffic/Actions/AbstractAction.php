<?php
namespace Traffic\Actions;

use Core\Sandbox\SandboxContext;
use Core\Application\Exception\Error;
use Core\Locale\LocaleService;
use Exception;
use Traffic\Macros\MacrosProcessor;
use Traffic\Pipeline\Payload;
use Traffic\Response\ContentType;
use Traffic\Response\Response;
use Traffic\Response\ResponseFactory;

abstract class AbstractAction
{
    protected $_name;

    protected $_weight;

    protected $_response;

    const CAMPAIGNS = 'campaigns';

    const STREAMS = 'streams';

    const URL = 'url';

    const TEXT = 'text';

    const NOTHING = 'nothing';

    const TYPE_REDIRECT = 'redirect';

    const TYPE_OTHER = 'other';

    const TYPE_HIDDEN = 'hidden';

    const UPLOAD = 'upload';

    /**
     * @var Payload
     */
    private $_pipelinePayload;

    public function getResponse()
    {
        return $this->getPipelinePayload()->getResponse();
    }

    public function setResponse(Response $response)
    {
        $this->getPipelinePayload()->setResponse($response);
    }

    public function getType()
    {
        return self::TYPE_REDIRECT;
    }

    /**
     * @return Payload
     * @throws Error
     */
    public function run()
    {
        $this->_execute();
        return $this->getPipelinePayload();
    }

    public function setPipelinePayload(Payload $payload)
    {
        $this->_pipelinePayload = $payload;
    }

    public function getPipelinePayload()
    {
        return $this->_pipelinePayload;
    }

    public function getRawActionPayload()
    {
        return $this->getPipelinePayload()->getActionPayload();
    }

    public function getActionPayload()
    {
        return $this->processMacros($this->getRawActionPayload());
    }

    public function getActionOptions()
    {
        return $this->getPipelinePayload()->getActionOptions();
    }

    protected function _execute()
    {
        $className = get_called_class();
        throw new Error("Method '_execute' must be implemented in {$className}");
    }

    public function setInfo($name, $weight)
    {
        $this->_name = $name;
        $this->_weight = $weight;
    }

    public function getField()
    {
        return self::URL;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getWeight()
    {
        return $this->_weight;
    }

    public function setContent($content)
    {
        $this->setResponse($this->getResponse()->withBody(ResponseFactory::safeBody($content)));
    }

    public function getRawClick()
    {
        return $this->getPipelinePayload()->getRawClick();
    }

    public function getCampaign()
    {
        return $this->getPipelinePayload()->getCampaign();
    }

    public function getStream()
    {
        return $this->getPipelinePayload()->getStream();
    }

    public function getLanding()
    {
        return $this->getPipelinePayload()->getLanding();
    }

    public function getOffer()
    {
        return $this->getPipelinePayload()->getOffer();
    }

    public function setContentType($value)
    {
        $this->header(ContentType::HEADER, $value);
    }

    public function addHeader($headerString)
    {
        if (!strstr($headerString, ':')) {
            throw new Exception("Header must contain ':'");
        }
        $header = ResponseFactory::parseHeaderString($headerString);
        $this->header(trim($header['name']), trim($header['value']));
    }

    public function header($name, $value)
    {
        $this->setResponse($this->getResponse()->withHeader($name, $value));
    }

    public function setStatus($status)
    {
        $this->setResponse($this->getResponse()->withStatus($status));
    }

    public function redirect($url)
    {
        $this->header('Location', $url);
        $this->setStatus(302);
    }

    public function setDestinationInfo($value)
    {
        $this->getRawClick()->setDestination($value);
    }

    public function getServerRequest()
    {
        return $this->getPipelinePayload()->getServerRequest();
    }

    protected function _executeInContext()
    {
        $from = null;
        foreach ($this->getServerRequest()->getQueryParams() as $paramName => $paramValue) {
            if (strpos($paramName, 'frm') === 0) {
                $from = $paramValue;
                break;
            }
        }

        switch (true) {
            case strpos($from, 'script') === 0:
                $this->_executeForScript();
                break;
            case strpos($from, 'frame') === 0:
                $this->_executeForFrame();
                break;
            default:
                $this->_executeDefault();
        }
    }

    protected function _executeForScript()
    {
        $this->addHeader('Content-type: application/javascript; charset=utf-8');
        $error = LocaleService::t('stream_actions.action_incompatible');
        $this->setContent('window.console && console.error("' . $error .'");');
    }

    protected function _executeForFrame()
    {
        $error = LocaleService::t('stream_actions.action_incompatible');
        $this->setContent('<script>window.console && console.error("' . $error .'");</script>');
    }

    protected function _executeDefault()
    {
        $this->setContent('Error! Method "_executeDefault()" must be implemented in "' . get_called_class() . '"');
    }

    protected function processMacros($body)
    {
        $pageContext = new SandboxContext([
            'server_request' => $this->getServerRequest(),
            'campaign' => $this->getCampaign(),
            'stream' => $this->getStream(),
            'raw_click' => $this->getRawClick()
        ]);
        return MacrosProcessor::process($pageContext, $body);
    }
}
