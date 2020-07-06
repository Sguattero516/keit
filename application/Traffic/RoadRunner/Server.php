<?php
namespace Traffic\RoadRunner;

use Component\System\Service\StatusService;
use Core\Application\Application;
use Core\Db\Db;
use Core\Kernel\Kernel;
use Core\Router\TrafficRouter;
use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\ConsoleRenderer;
use Spiral\Goridge\Exceptions\RelayException;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Traffic\Logging\Service\LoggerService;
use Traffic\Request\ServerRequestFactory;
use Traffic\Response\Response;

class Server
{
    const SERVER_HOST = "127.0.0.1";
    const SERVER_PC_PORT = 6001;
    const RPC_COMMAND_RESET = "http.Reset";
    const MIN_PHP_VERSION = '7.1';

    private $_psrClient;
    private $_dumper;

    public function __construct()
    {
        static::checkDependencies();
        $this->_setupPhp();
        $this->_psrClient = $this->_buildPsr7Client();
        $this->_dumper = $this->_buildDumper();
    }

    public static function checkDependencies($phpVersion = null)
    {
        if (empty($phpVersion)) {
            $phpVersion = phpversion();
        }

        if (version_compare($phpVersion, self::MIN_PHP_VERSION) < 0) {
            throw new \Exception("[RR] PHP version must be 7.1 or higher");
        }

        if (!extension_loaded('sockets')) {
            throw new \Exception("[RR] PHP extension 'sockets' is required!");
        }
    }

    public static function restart()
    {
        if (Application::instance()->isRoadRunnerRunning() || StatusService::instance()->isEngineRoadRunner()) {
            try {
                static::checkDependencies();
            } catch (\Exception $e) {
                LoggerService::instance()->warning('The tracker is incompatible with RoadRunner' . $e->getMessage());
            }
            try {
                $rpc = new RPC(new SocketRelay(self::SERVER_HOST, self::SERVER_PC_PORT));
                $rpc->call(self::RPC_COMMAND_RESET, true);
                return true;
            } catch (RelayException $e) {
                LoggerService::instance()->warning('RoadRunner RPC message: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param TrafficRouter $router
     */
    public function start(TrafficRouter $router)
    {
        while ($psr7 = $this->_psrClient->acceptRequest()) {
            try {
                ServerRequestFactory::clearSuperGlobals();
                $request = ServerRequestFactory::fromPsr7Request($psr7);

                $routerResult = $router->match($request);

                $response = Kernel::run($routerResult->serverRequest(), $routerResult->context());

                $this->_logResponse($response);
                $this->_psrClient->respond($response);
            } catch (\Throwable $e) {
                $this->_processException($e);
            }
            LoggerService::instance()->flush();
        }
    }

    private function _processException(\Throwable $exception)
    {
        LoggerService::instance()->error((string) $exception);

        if (Application::instance()->isDebug()) {
            $errorMessage = (string) $exception;
        } else {
            $errorMessage = json_encode(['error' => 'Internal error (RR). Please open the system log.']);
        }

        $this->_dumper->dump((string) $exception, Dumper::ERROR_LOG);
        $this->_psrClient->getWorker()->error($errorMessage);

        try {
            Db::instance()->disconnect();
        } catch (\Exception $e) {
            LoggerService::instance()->warning('[Server] ' . $e->getMessage());
        }
    }

    private function _logResponse(Response $response)
    {
        if (LoggerService::instance()->getLevel() !== LoggerService::DEBUG) {
            return ;
        }

        $this->_dumper->dump($response->serialize(), Dumper::ERROR_LOG);
    }

    private function _setupPhp()
    {
        ini_set('display_errors', 'stderr');
    }

    private function _buildPsr7Client()
    {
        $worker = new Worker(new StreamRelay(STDIN, STDOUT));
        return new PSR7Client($worker);
    }

    private function _buildDumper()
    {
        $dumper = new Dumper();
        $dumper->setRenderer(Dumper::ERROR_LOG, new ConsoleRenderer());
        return $dumper;
    }
}