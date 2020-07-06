<?php
namespace Traffic\Actions\Service;

use Core\Sandbox\SandboxContext;
use Traffic\Actions\Repository\StreamActionRepository;
use Traffic\Macros\MacrosProcessor;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\Model\Conversion;
use Traffic\RawClick;
use Traffic\Request\ServerRequest;
use Traffic\Service\AbstractService;

class StreamActionService extends AbstractService
{
    const DESTINATION_LIMIT = 50;
    const TRUNCATED = '... (truncated)';

    public function buildDestination(ServerRequest $serverRequest, Campaign $campaign, $url, BaseStream $stream = null, RawClick $rawClick, Conversion $conversion = null)
    {
        if (empty($stream)) {
            $stream = new BaseStream();
        }

        $action = StreamActionRepository::instance()->getNewActionInstance($stream->getActionType());

        if (method_exists($action, 'destination')) {
            $destination = call_user_func_array(array($action, 'destination'), array($stream, $rawClick));
        } else {
            $pageContext = new SandboxContext([
                'server_request' => $serverRequest,
                'stream' => $stream,
                'raw_click' => $rawClick,
                'conversion' => $conversion,
                'campaign' => $campaign
            ]);

            $destination = MacrosProcessor::process($pageContext, $url);
        }
        return $destination;
    }

    public function truncateDestination($text)
    {
        if (mb_strlen($text) > self::DESTINATION_LIMIT) {
            $text = mb_substr($text, 0, self::DESTINATION_LIMIT) . self::TRUNCATED;
        }
        return $text;
    }
}