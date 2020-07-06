<?php
namespace Traffic\Pipeline\Stage;

use DomainException;
use Traffic\Actions\AbstractAction;
use Traffic\Actions\ActionError;
use Traffic\Actions\Repository\StreamActionRepository;
use Traffic\BackCompatibility\BackCompatibility;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Pipeline\Payload;

class ExecuteActionStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $stream = $payload->getStream();
        $rawClick = $payload->getRawClick();
        $actionType = $payload->getActionType();

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if (empty($actionType)) {
            $msg = 'Empty actionType in campaign #';
            if ($payload->getCampaign()) {
                $msg .= $payload->getCampaign()->getId();
            }
            $logEntry->add($msg);
            return $payload;
        }
        if (!$payload->getResponse()) {
            throw new StageException('Empty response in payload');
        }

        $logEntry->add('Executing action "' . $actionType . '"');

        try {
            /** @var $action AbstractAction|\Component\StreamActions\AbstractAction */
            $action = StreamActionRepository::instance()->getNewActionInstance($actionType);
        } catch (ActionError $e) {
            throw new StageException($e->getMessage());
        }

        if (empty($action)) {
            throw new StageException('Incorrect type "' . $actionType . '" in stream #' . $stream->getId());
        }

        if (BackCompatibility::isLegacyAction($action)) {
            $action->setResponse($payload->getResponse());
            $action->setPipelinePayload($payload);
            $response = BackCompatibility::executeLegacyAction($action, $payload);
            $payload->setResponse($response);
        } else {
            if (!($action instanceof AbstractAction)) {
                throw new StageException('Redirect "' . get_class($action) . '" must be instance of AbstractAction');
            }
            $action->setPipelinePayload($payload);
            try {
                $payload = $action->run();
            } catch (DomainException $e) {
                throw new StageException($e->getMessage());
            }
        }

        return $payload;
    }
}
