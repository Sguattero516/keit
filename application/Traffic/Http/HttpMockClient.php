<?php
namespace Traffic\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Traffic\Http\Error\CatchedRequestException;

class HttpMockClient implements ClientInterface
{
    /**
     * @var array[Request, Response]
     */
    private $_stubs = [];
    private $_requested = [];

    /**
     * @param $request Request|string
     * @param $response ResponseInterface|string|int
     */
    public function stub($request, $response)
    {
        if (is_string($request)) {
            $request = new Request('GET', $request);
        }
        if (is_string($response)) {
            $response = new Response(200, [], $response);
        }

        if (is_integer($response)) {
            $response = new Response($response, [], '');
        }

        array_unshift($this->_stubs, [$request, $response]);
    }

    public function stubAll($response)
    {
        if (is_string($response)) {
            $response = new Response(200, [], $response);
        }

        $this->_stubs[] = [null, $response];
    }

    public function stubHttpThrowException($request, $status)
    {
        if (is_string($request)) {
            $request = new Request('GET', $request);
        }

        $response = new Response($status, []);

        $error = new RequestException("stub error '{$status}'", $request, $response);

        $this->stub($request, $error);
    }

    public function httpRequestHasBeenMade($url)
    {
        return !empty($this->_requested[$url]);
    }

    public function flushStubs()
    {
        $this->_requested = [];
        $this->_stubs = [];
    }

    public function send(RequestInterface $request, array $options = [])
    {
        $current = [];

        foreach ($this->_stubs as $row) {
            /**
             * @var $_request Request
             * @var $_response Response|RequestException
             */
            list($_request, $_response) = $row;

            if (is_null($_request)) {
                $response = $_response;
                break;
            }

            if ((string) $_request->getUri() == (string) $request->getUri()
                && $_request->getMethod() == $request->getMethod()) {
                $response = $_response;
                break;
            }
            $current[] = "{$_request->getMethod()} {$_request->getUri()}";
        }
        if (empty($response)) {
            $message = 'External requests are disabled for testing. '.
                'Please make a stub:' . PHP_EOL .
                '  $this->stubHttp(\'' . $request->getUri() . '\', \'body\')'. PHP_EOL .
                'OR instance of GuzzleHttp\Psr7\Request and GuzzleHttp\Psr7\Response' . PHP_EOL .
                '  $this->stubHttp(Request $request, Response $response)' . PHP_EOL .  PHP_EOL .
                'Current: ' . PHP_EOL . '- ' .
                implode(PHP_EOL . '- ', $current);

            throw new CatchedRequestException($message);
        }
        $this->_requested[(string) $request->getUri()] = true;
        if ($response instanceof RequestException) {
            throw $response;
        }
        return $response;
    }

    /**
     * @param $uri string
     * @param $destination string
     * @param $headers array|null
     * @param $options array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function download($uri, $destination, $headers = [], $options = [])
    {
        $request = new Request('get', $uri, $headers);
        $response = $this->send($request, $options);
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($destination, $response->getBody());
    }

    public function sendAsync(RequestInterface $request, array $options = [])
    {
        $response = $this->send($request, $options);
        $promise = new Promise(function () use (&$promise, $response) {
            $promise->resolve($response);
        });
        $promise->then(function ($response) {
            return $response;
        });
        return $promise;
    }

    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \Exception("Not Implemented");
    }

    public function getConfig($option = null)
    {
        throw new \Exception("Not Implemented");
    }

    public function request($method, $uri, array $options = [])
    {
        throw new \Exception("Not Implemented");
    }
}