<?php
namespace Traffic\Response;

use GuzzleHttp\Psr7\MessageTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends \GuzzleHttp\Psr7\Response implements ResponseInterface
{
    /**
     * @param array $options
     * @return Response
     * @throws ResponseError
     */
    public static function build($options = [])
    {
        return ResponseFactory::build($options);
    }

    /**
     * Возвращает Response с content-type: application/json
     * @param array $options
     * @return MessageTrait|Response
     * @throws ResponseError
     */
    public static function buildJson($options = [])
    {
        $response = static::build($options);
        $response = $response->withHeader(ContentType::HEADER, ContentType::JSON);
        return $response;
    }
    /**
     * Возвращает Response с content-type: text/html
     * @param array $options
     * @return MessageTrait|Response
     * @throws ResponseError
     */
    public static function buildHtml($options = [])
    {
        $response = static::build($options);
        $response = $response->withHeader(ContentType::HEADER, ContentType::HTML);
        return $response;
    }

    /**
     * @param string $header
     * @param string|string[] $value
     * @return static|MessageTrait|Response
     */
    public function withHeader($header, $value)
    {
        return parent::withHeader($header, $value);
    }

    /**
     * @param StreamInterface $body
     * @return static|MessageTrait|\GuzzleHttp\Psr7\Response|Response
     */
    public function withBody(StreamInterface $body)
    {
        return parent::withBody($body);
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return \GuzzleHttp\Psr7\Response|ResponseInterface|static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return parent::withStatus($code, $reasonPhrase);
    }

    public function serialize()
    {
        $data = [
            'status' => $this->getStatusCode(),
            'headers' => $this->getHeaders(),
            'body' => (string) $this->getBody(),
        ];
        return json_encode($data);
    }

    /**
     * @return MessageTrait|Response
     */
    public function disableCache()
    {
        return $this
            ->withHeader('Last-Modified',  gmdate('D, d M Y H:i:s') . ' GMT')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate,post-check=0,pre-check=0')
            ->withHeader('Pragma','no-cache')
            ->withHeader('Expires', '0');
    }
}