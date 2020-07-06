<?php
namespace Traffic\Request;

use GuzzleHttp\Psr7\LazyOpenStream;
use Traffic\Device\Service\RealRemoteIpService;
use Traffic\RoadRunner\Server;
use function GuzzleHttp\Psr7\parse_query;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;

class ServerRequestFactory
{
    const HTTPS = 'https';
    const HTTP = 'http';

    private static $_allowedOptions = [
        'parsed_body', 'query_params', 'server_params', 'uploaded_files', 'cookie_params', 'body', 'headers', 'force_ajax',
        'protocol_version', 'method', 'request_target', 'attributes', 'uri'
    ];

    public static function fromPsr7Request(ServerRequestInterface $psr7)
    {
        return static::build([
            'headers' => $psr7->getHeaders(),
            'body' => $psr7->getBody(),
            'parsed_body' => $psr7->getParsedBody(),
            'protocol_version' => $psr7->getProtocolVersion(),
            'method' => $psr7->getMethod(),
            'request_target' => $psr7->getRequestTarget(),
            'query_params' => $psr7->getQueryParams(),
            'cookie_params' => $psr7->getCookieParams(),
            'uploaded_files' => $psr7->getUploadedFiles(),
            'server_params' => $psr7->getServerParams(),
            'uri' => $psr7->getUri()
        ]);
    }

    /**
     * @param array $options
     * @return ServerRequest
     * @throws ServerRequestError
     */
    public static function build($options = [])
    {
        foreach ($options as $key => $value) {
            if (!in_array($key, self::$_allowedOptions)) {
                throw new ServerRequestError("Incorrect option {$key}");
            }
        }

        /**
         * @var $serverRequest ServerRequest
         */
        $serverRequest = ServerRequest::fromGlobals();

        if (isset($options['protocol_method'])) {
            $serverRequest = $serverRequest->withProtocolVersion($options['protocol_method']);
        }

        if (isset($options['method'])) {
            $serverRequest = $serverRequest->withMethod($options['method']);
        }

        if (isset($options['request_target'])) {
            $serverRequest = $serverRequest->withRequestTarget($options['request_target']);
        }

        if (isset($options['server_params'])) {
            $serverRequest = $serverRequest->withServerParams($options['server_params']);
        }

        if (isset($options['uploaded_files'])) {
            $serverRequest = $serverRequest->withUploadedFiles(\GuzzleHttp\Psr7\ServerRequest::normalizeFiles($options['uploaded_files']));
        }

        if (isset($options['headers'])) {
            $serverRequest = $serverRequest->withHeaders($options['headers']);
        }

        if (empty($options['body']) && !$serverRequest->getBody()) {
            $options['body'] = new LazyOpenStream(ServerRequest::DEFAULT_BODY, 'r+');
        }

        if (isset($options['force_ajax']) && $options['force_ajax']) {
            $serverRequest = $serverRequest->withHeader(ServerRequest::HEADER_X_REQUESTED_WITH, ServerRequest::XMLHTTPREQUEST);
        }

        if (isset($options['body']) && trim($options['body'])) {
            // TODO попробовать instanceof StreamInterface
            if (!$options['body'] instanceof LazyOpenStream && !$options['body'] instanceof Stream) {
                $class = is_object($options['body']) ? get_class($options['body']) : gettype($options['body']);
                throw new \Exception("ServerRequest body must be instance of LazyOpenStream, but got " . $class);
            }
            $serverRequest = $serverRequest->withBody($options['body']);
        }

        if ($serverRequest->getBody()) {
            $parsedBody = ServerRequestFactory::parseBody($serverRequest->getBody());
            if ($parsedBody) {
                $serverRequest = $serverRequest->withParsedBody($parsedBody);
            }
        }


        if (isset($options['query_params'])) {
            $serverRequest = $serverRequest->withQueryParams($options['query_params']);
        }

        if (isset($options['cookie_params'])) {
            $serverRequest = $serverRequest->withCookieParams($options['cookie_params']);
        }

        if (isset($options['parsed_body'])) {
            $serverRequest = $serverRequest->withParsedBody($options['parsed_body']);
        }

        if (isset($options['uri'])) {
            $serverRequest = $serverRequest->withUri(new Uri((string) $options['uri']));
        }

        $serverRequest = self::fixUri($serverRequest);
        $serverRequest = self::fixRequestMethod($serverRequest);
        $serverRequest = self::fixServerRequestUri($serverRequest);
        $serverRequest = self::fixRealIp($serverRequest);
        $serverRequest = self::fixServerRequestName($serverRequest);

        return $serverRequest;
    }

    /**
     * Тело запроса может быть json или application/x-www-form-urlencoded
     * @param StreamInterface $body
     * @return array|null
     */
    public static function parseBody(StreamInterface $body)
    {
        $postData = (string) $body;
        if (strlen($postData) && in_array($postData[0], ['{', '['])) {
            return json_decode($postData, true);
        }
        if (strlen($postData) && strstr($postData, '&')) {
            return parse_query($postData);
        }
        return null;
    }

    /**
     * Набор исправлений URI под RR, и прокси Cloudflare
     * @param ServerRequest $serverRequest
     * @return \GuzzleHttp\Psr7\Request|\Psr\Http\Message\RequestInterface|ServerRequest
     */
    private static function fixUri(ServerRequest $serverRequest)
    {
        $uri = $serverRequest->getUri();

        if ($serverRequest->getServerParam(self::HTTPS)) {
            $uri = $uri->withScheme(self::HTTPS);
        }

        // Фикс хоста
        if ($serverRequest->getHeaderLine(ServerRequest::HEADER_HOST)) {
            $uri = $uri->withHost($serverRequest->getHeaderLine(ServerRequest::HEADER_HOST));
        }

        if ($serverRequest->getHeaderLine(ServerRequest::HEADER_X_REAL_HOST)) {
            $host = $serverRequest->getHeaderLine(ServerRequest::HEADER_X_REAL_HOST);
            $uri = $uri->withHost($host);
        }

        // Фикс для CF, когда снаружи запрос шел по https, а между CF и сервером по http
        if ($serverRequest->getHeaderLine(ServerRequest::HEADER_X_FORWARDED_PROTO) == self::HTTPS) {
            $uri = $uri->withScheme(self::HTTPS);
        }
        $serverRequest = $serverRequest->withUri($uri);

        return $serverRequest;
    }

    /**
     * Актуализация REQUEST_URI и QUERY_STRING в serverParams
     *
     **/
    private static function fixServerRequestUri(ServerRequest $serverRequest)
    {
        $uri = $serverRequest->getUri();
        $query = $uri->getPath();
        if ($uri->getQuery()) {
            $query .= "?{$uri->getQuery()}";
        }

        $serverRequest =  $serverRequest->withServerParams([
            'REQUEST_URI' => $query,
            'QUERY_STRING' => $query,
        ]);
        return $serverRequest;
    }

    public static function clearSuperGlobals()
    {
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
    }

    /**
     * Это нужно, чтобы пользовательскте скрипты видели актуальные данные
     * @param ServerRequest $serverRequest
     */
    public static function extractSuperGlobals(ServerRequest $serverRequest)
    {
        $_GET = $serverRequest->getQueryParams();
        $_POST = $serverRequest->getParsedBody();
        $_COOKIE = $serverRequest->getCookieParams();
        $_SERVER = $serverRequest->getServerParams();
    }

    /**
     * Замена REMOTE_ADDR настоящим адресом
     * @param ServerRequest $serverRequest
     * @return \GuzzleHttp\Psr7\ServerRequest|ServerRequestInterface|ServerRequest
     */
    private static function fixRealIp(ServerRequest $serverRequest)
    {
        return $serverRequest->withServerParams([
            ServerRequest::ORIGINAL_REMOTE_ADDR => $serverRequest->getServerParam(ServerRequest::REMOTE_ADDR),
            ServerRequest::REMOTE_ADDR => RealRemoteIpService::instance()->find($serverRequest)
        ]);
    }

    /**
     * В режиме RR параметр method содержит неправильное значение
     * @param ServerRequest $serverRequest
     * @return \GuzzleHttp\Psr7\Request|\Psr\Http\Message\RequestInterface|ServerRequest
     */
    private static function fixRequestMethod(ServerRequest $serverRequest)
    {
        if ($serverRequest->getServerParam('REQUEST_METHOD')) {
            $serverRequest = $serverRequest->withMethod($serverRequest->getServerParam('REQUEST_METHOD'));
        }
        return $serverRequest;
    }

    /**
     * Актуализация SERVER_NAME в serverParams
     * @param ServerRequest $serverRequest
     * @return \GuzzleHttp\Psr7\ServerRequest|ServerRequestInterface|ServerRequest
     */
    private static function fixServerRequestName(ServerRequest $serverRequest)
    {
        $uri = $serverRequest->getUri();
        $host = $uri->getHost();

        $serverRequest =  $serverRequest->withServerParams([
            'SERVER_NAME' => $host
        ]);
        return $serverRequest;
    }
}
