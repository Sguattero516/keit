<?php
namespace Traffic\Response;


use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\StreamInterface;

class ResponseFactory
{
    const DEFAULT_STATUS = 200;

    private static $_allowedOptions = ['headers', 'body', 'status', 'disable_cache'];

    /**
     * @param array $options
     * @return Response
     * @throws ResponseError
     */
    public static function build($options = [])
    {
        foreach ($options as $key => $value) {
            if (!in_array($key, static::$_allowedOptions)) {
                throw new ResponseError("Incorrect option {$key}");
            }
        }

        if (!empty($options['headers'])) {
            $headers = $options['headers'];
        } else {
            $headers = [];
        }

        $status = isset($options['status']) ? $options['status'] : self::DEFAULT_STATUS;
        $response = new Response($status, $headers);

        if (isset($options['disable_cache'])) {
            $response = $response->disableCache();
        }

        if (!empty($options['body'])) {
            $response = $response->withBody(ResponseFactory::safeBody($options['body']));
        }

        return $response;
    }

    public static function safeBody($rawBody)
    {
        if ($rawBody instanceof StreamInterface) {
            return $rawBody;
        }
        if (is_array($rawBody) || is_object($rawBody)) {
            $body = json_encode($rawBody);
        } else {
            $body = $rawBody;
        }
        return stream_for($body);
    }

    /**
     * Парсит строку вида "Header: value" на [name => ...,  value => ...]
     * @param $headerString
     * @return array [name => ...,  value => ...]
     */
    public static function parseHeaderString($headerString)
    {
        $pos = strpos($headerString, ':');
        $value = substr($headerString, $pos + 1);
        $name = substr($headerString, 0, $pos);
        return ['name' => trim($name), 'value' => trim($value)];
    }
}