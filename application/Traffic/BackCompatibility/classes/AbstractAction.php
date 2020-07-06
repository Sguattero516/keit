<?php
namespace Component\StreamActions;

use Component\Clicks\Model\RawClick;
use Component\Streams\Model\BaseStream;
use Core\Application\Exception\Error;
use Core\Locale\LocaleService;
use Traffic\Pipeline\Payload;
use Traffic\Response\ContentType;
use Traffic\Response\Response;
use Traffic\Response\ResponseFactory;
use Traffic\Response\StatusCode;

/**
 * @deprecated
 */
class AbstractAction
{
    protected $_name;

    protected $_weight;

    const CAMPAIGNS = 'campaigns';

    const STREAMS = 'streams';

    const URL = 'url';

    const TEXT = 'text';

    const NOTHING = 'nothing';

    const TYPE_REDIRECT = 'redirect';

    const TYPE_OTHER = 'other';

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var Payload
     */
    private $_pipelinePayload;

    public function getResponse()
    {
        return $this->_response;
    }

    public function setResponse(Response $response)
    {
        $this->_response = $response;
    }

    public function setPipelinePayload(Payload $payload)
    {
        $this->_pipelinePayload = $payload;
    }

    public function getPipelinePayload()
    {
        return $this->_pipelinePayload;
    }

    public function getType()
    {
        return self::TYPE_REDIRECT;
    }

    /**
     * @param BaseStream $stream
     * @param RawClick $rawClick
     * @return Response
     */
    public function run(BaseStream $stream, RawClick $rawClick)
    {
        $this->_execute($stream, $rawClick);
        return $this->getResponse();
    }

    protected function _execute(BaseStream $stream, RawClick $rawClick)
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

    public function getCharset()
    {
        // do nothing
    }

    public function setCharset($charset)
    {
        // do nothing
    }

    public function setContentType($value)
    {
        $this->setResponse($this->getResponse()->withHeader(ContentType::HEADER, $value));
    }

    public function addHeader($header)
    {
        $pos = strpos($header, ':');
        $value = substr($header, $pos + 1);
        $name = substr($header, 0, $pos);
        $this->setResponse($this->getResponse()->withHeader(trim($name), trim($value)));
    }

    public function redirect($url)
    {
        $newResponse = $this->getResponse()
            ->withHeader('Location', $url)
            ->withStatus(StatusCode::MOVED_TEMPORARILY);
        $this->setResponse($newResponse);
    }

    public function getServerRequest()
    {
        return $this->getPipelinePayload()->getServerRequest();
    }

    protected function _executeInContext(BaseStream $stream, RawClick $visitor)
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
                $this->_executeForScript($stream, $visitor);
                break;
            case strpos($from, 'frame') === 0:
                $this->_executeForFrame($stream, $visitor);
                break;
            default:
                $this->_executeDefault($stream, $visitor);
        }
    }

    protected function _executeForScript(BaseStream $stream, RawClick $rawClick)
    {
        $this->addHeader('Content-type: application/javascript; charset=utf-8');
        $error = LocaleService::t('stream_actions.action_incompatible');
        $this->setContent('window.console && console.error("' . $error .'");');
    }

    protected function _executeForFrame(BaseStream $stream, RawClick $rawClick)
    {
        $error = LocaleService::t('stream_actions.action_incompatible');
        $this->setContent('<script>window.console && console.error("' . $error .'");</script>');
    }

    protected function _executeDefault(BaseStream $stream, RawClick $rawClick)
    {
        $this->setContent('Error! Method "_executeDefault()" must be implemented in "' . get_called_class() . '"');
    }
}
