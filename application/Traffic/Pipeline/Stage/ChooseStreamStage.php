<?php
namespace Traffic\Pipeline\Stage;

use Core\Exceptions\NotFoundError;
use Exception;
use Traffic\Actions\StreamRotator;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\Model\Stream;
use Traffic\Pipeline\Payload;
use Traffic\Repository\CachedStreamRepository;
use Traffic\Response\ResponseFactory;
use Traffic\Response\StatusCode;

class ChooseStreamStage implements StageInterface
{
    const NO_STREAM_SELECTED = 'No stream selected';

    const SHOW_TEXT = 'show_text';

    const DO_NOTHING = 'do_nothing';

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $serverRequest = $payload->getServerRequest();
        $response = $payload->getResponse();

        if (empty($response)) {
            throw  new StageException('response is not set');
        }

        if (empty($serverRequest)) {
            throw new StageException('server_request is not defined');
        }

        if (empty($campaign)) {
            throw new StageException('campaign is not defined');
        }

        if (empty($rawClick)) {
            throw new StageException('rawClick is not defined');
        }

        $forcedStreamId = $payload->getForcedStreamId();
        if ($forcedStreamId) {
            try {
                $logEntry->add('Loading stream #' . $forcedStreamId);
                $stream = CachedStreamRepository::instance()->findCachedStream($forcedStreamId);
            } catch (NotFoundError $e) {
                $logEntry->add($e->getMessage());
                return $this->_triggerNotFound($payload, $logEntry);
            }
        }

        $groupedStreams = CachedStreamRepository::instance()->getCachedActiveStreams($campaign);

        if (empty($stream) || $stream->isDeleted()) {
            $logEntry->add('Processing campaign ' . $campaign->getId());
            $streams = $groupedStreams->byType(Stream::TYPE_FORCED);

            $rotator = new StreamRotator($campaign, $logEntry, $rawClick);
            $stream = $rotator->chooseByPosition($serverRequest, $streams);
        }

        if (empty($stream) || $stream->isDeleted()) {
            $streams = $groupedStreams->byType(Stream::TYPE_REGULAR);
            $rotator = new StreamRotator($campaign, $logEntry, $rawClick);

            if ($campaign->getType() == Campaign::TYPE_POSITION) {
                $stream = $rotator->chooseByPosition($serverRequest, $streams);
            } else {
                $stream = $rotator->chooseByWeight($serverRequest, $streams);

                if (!empty($stream) && $campaign->isBindVisitorsEnabled()) {
                    list($serverRequest, $response) = $rotator->bindToStream($serverRequest, $response, $stream);
                    list($serverRequest, $response) = $rotator->saveIp($serverRequest, $response, $rawClick->getIpString());

                    $payload->setServerRequest($serverRequest);
                    $payload->setResponse($response);
                }
            }
        }

        if (empty($stream) || $stream->isDeleted()) {
            $streams = $groupedStreams->byType(Stream::TYPE_DEFAULT);
            $stream = (empty($streams) ? null : $streams[0]);
        }

        if (!empty($stream) && !$stream->isDeleted()) {
            $payload->setStream($stream);
            $payload->getRawClick()->setStreamId($stream->getId());
            if ($stream->getSchema() != BaseStream::LANDINGS && $stream->getSchema() != BaseStream::OFFERS) {
                $payload->setActionType($stream->getActionType());
                $payload->setActionPayload($stream->getActionPayload());
                $payload->setActionOptions($stream->getActionOptions());
            }
        } else {
            $this->_setNoDirection($payload);
        }
        return $payload;
    }

    private function _setNoDirection(Payload $payload)
    {
        $payload->setActionType(self::DO_NOTHING);
    }

    /**
     * @param Payload $payload
     * @param TrafficLogEntry $logger
     * @return Payload
     * @throws Exception
     */
    private function _triggerNotFound(Payload $payload, TrafficLogEntry $logger)
    {
        $response = $payload->getResponse()
            ->withStatus(StatusCode::NOT_FOUND)
            ->withBody(ResponseFactory::safeBody('Forced stream not found'));
        $logger->add('Forced stream not found. Shows 404 NotFound');
        $payload->setResponse($response);
        $payload->abort();

        return $payload;
    }
}
