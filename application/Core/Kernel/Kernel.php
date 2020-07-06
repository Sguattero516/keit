<?php
namespace Core\Kernel;

use Core\Context\ContextInterface;
use Core\Db\Db;
use Traffic\Logging\Service\LoggerService;
use Traffic\Request\ServerRequest;
use Traffic\Request\ServerRequestFactory;
use Traffic\Response\Response;
use Traffic\Tools\Tools;

/**
 * Проводит выполнение методов переденного Context'а:
 *  1. bootstrap - подготовка приложения к работе
 *  2. modifyRequest - проводит манипуляции с объектом ServerRequest
 *  3. dispatcher - конструктор Dispatcher'а
 *  4. shutdown - завершает работу приложения
 */
class Kernel
{
    /**
     * @param $serverRequest ServerRequest
     * @param $context ContextInterface
     * @return Response
     */
    public static function run(ServerRequest $serverRequest, ContextInterface $context)
    {
        $kernel = new Kernel();
        return $kernel->runApplication($serverRequest, $context);
    }

    /**
     * @param ServerRequest $serverRequest
     * @param ContextInterface $context
     * @return Response|null
     */
    public function runApplication(ServerRequest $serverRequest, ContextInterface $context)
    {
        try {
            if (empty($serverRequest)) {
                throw new \Exception('serverRequest is not set');
            }

            if (empty($context)) {
                throw new \Exception('Context must be provided');
            }

            $context->bootstrap();

            $this->_updateLoggerContext($context);

            $serverRequest = $context->modifyRequest($serverRequest);

            ServerRequestFactory::extractSuperGlobals($serverRequest);

            $dispatcher = $context->dispatcher($serverRequest);
            if (!$dispatcher) {
                throw new \Exception("Context " . get_class($context) . ' must return a Dispatcher');
            }

            $response = $dispatcher->dispatch($serverRequest);
            if (empty($response)) {
                throw new \Exception(get_class($dispatcher) . '#dispatch must return a Response');
            }

            $context->shutdown();

            $this->_closeDbConnection();
        } catch (\Exception $exception) {
            $response = $context->handleException($exception, $serverRequest);
        }

        return $response;
    }

    private function _updateLoggerContext(ContextInterface $context)
    {
        $contextName = Tools::demodulize(strtolower(str_replace('Context', '', get_class($context))));
        LoggerService::instance()->setContextName($contextName);
    }

    private function _closeDbConnection()
    {
        Db::instance()->disconnect();
    }
}