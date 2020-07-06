<?php
namespace Traffic\Http\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Traffic\Http\HttpMockClient;
use Traffic\Service\AbstractService;
use function GuzzleHttp\Promise\each_limit;

class HttpService extends AbstractService
{
    /**
     * @var ClientInterface|HttpMockClient
     */
    private $_client;

    const HEADERS = 'headers';
    const DEFAULT_AGENT = 'Keitaro HTTP Client';

    public function client()
    {
        return $this->_client;
    }

    public function buildDefaultClient()
    {
        $client = new Client();
        $this->setClient($client);
    }

    public function setClient(ClientInterface $client)
    {
        $this->_client = $client;
    }

    /**
     * @param $uri string
     * @param $headers array|null
     * @param $options array|null
     * @return Response|mixed|ResponseInterface
     * @throws RequestException
     * @throws GuzzleException
     */
    public function get($uri, array $headers = [], array $options = [])
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException("'uri' must be a string (got: {$uri})");
        }
        $request = new Request('get', $uri, $this->_withDefaultHeaders($headers));
        return $this->send($request, $options);
    }

    public function getAsync($uri, array $headers = [], array $options = [])
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException("'uri' must be a string (got: {$uri})");
        }
        $request = new Request('get', $uri, $this->_withDefaultHeaders($headers));
        return $this->sendAsync($request, $options);
    }

    /**
     * @param $uri string
     * @param $headers array|null
     * @param $options array|null
     * @return Response|mixed|ResponseInterface
     * @throws RequestException
     * @throws GuzzleException
     */
    public function post($uri, array $headers = [], array $options = [])
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException("'uri' must be a string");
        }
        $request = new Request('post', $uri, $this->_withDefaultHeaders($headers));
        return $this->send($request, $options);
    }

    /**
     * @param $request Request
     * @param $options array|null
     * @return Response|mixed|ResponseInterface
     * @throws RequestException
     * @throws GuzzleException
     */
    public function send(Request $request, array $options = [])
    {
        $options = $this->_withOptionsDefaults($options);
        return $this->client()->send($request, $options);
    }

    public function sendAsync(Request $request, array $options = [])
    {
        $options = $this->_withOptionsDefaults($options);
        return $this->client()->sendAsync($request, $options);
    }

    /**
     * @param $uri string
     * @param $destination
     * @param $headers array|null
     * @param $options array|null
     * @return Response
     * @throws RequestException
     * @throws GuzzleException
     */
    public function download($uri, $destination, $headers = [], $options = [])
    {
        if ($this->client() instanceof HttpMockClient) {
            return $this->client()->download($uri, $destination, $headers, $options);
        }

        $request = new Request('get', $uri, $headers);

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $options['sink'] = $destination;
        return $this->send($request, $options);
    }

    /**
     * Пока в Guzzle не примут PR https://github.com/guzzle/promises/pull/70
     *
     * @param $promises
     * @param int|callable $concurrency
     * @return PromiseInterface
     */
    public static function settleLimit($promises, $concurrency)
    {
        $results = [];

        return each_limit(
            $promises,
            $concurrency,
            function ($value, $idx) use (&$results) {
                $results[$idx] = ['state' => PromiseInterface::FULFILLED, 'value' => $value];
            },
            function ($reason, $idx) use (&$results) {
                $results[$idx] = ['state' => PromiseInterface::REJECTED, 'reason' => $reason];
            }
        )->then(function () use (&$results) {
            ksort($results);
            return $results;
        });
    }

    private function _withDefaultHeaders($headers)
    {
        if (empty($headers)) {
            $headers = [];
        }
        $headers['UserAgent'] = self::DEFAULT_AGENT;
        return $headers;
    }

    private function _withOptionsDefaults($options = [])
    {
        if (empty($options[RequestOptions::VERIFY])) {
            $options[RequestOptions::VERIFY] = false;
        }

        if (!isset($options[RequestOptions::CONNECT_TIMEOUT])) {
            $options[RequestOptions::CONNECT_TIMEOUT] = 5;
        } elseif (empty($options[RequestOptions::CONNECT_TIMEOUT])) {
            unset($options[RequestOptions::CONNECT_TIMEOUT]);
        }

        if (!isset($options[RequestOptions::TIMEOUT])) {
            $options[RequestOptions::TIMEOUT] = 5;
        } elseif (empty($options[RequestOptions::TIMEOUT])) {
            unset($options[RequestOptions::TIMEOUT]);
        }

        return $options;
    }
}
