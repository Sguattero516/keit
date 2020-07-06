<?php

namespace BjoernGoetschke\Psr7Cookies;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Reads cookies from a {@see ServerRequestInterface}.
 *
 * @api usage
 * @copyright BSD-2-Clause, see LICENSE.txt and README.md files provided with the library source code
 */
class HttpRequestCookies {

    /**
     * The request object the cookies will be read from.
     *
     * @var ServerRequestInterface
     */
    private $request = null;

    /**
     * Constructor.
     *
     * @param ServerRequestInterface $request
     *        The request object the cookies will be read from.
     */
    public function __construct(ServerRequestInterface $request) {

        $this->request = $request;

    }

    /**
     * Destructor.
     */
    public function __destruct() {
    }

    /**
     * Clone.
     */
    public function __clone() {
    }

    /**
     * Prevent serialize.
     *
     * @codeCoverageIgnore
     */
    private function __sleep() {
    }

    /**
     * Returns true if the specified cookie exists, otherwise false.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @return bool
     */
    public function hasCookie($name) {

        return array_key_exists($name, $this->request->getCookieParams());

    }

    /**
     * Get the value of the specified cookie, returns an empty string if the cookie does not exist.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @return string
     */
    public function getValue($name) {

        if (!$this->hasCookie($name)) {
            return '';
        }

        return (string)$this->request->getCookieParams()[$name];

    }

    /**
     * Get the original request object.
     *
     * @api usage
     * @return ServerRequestInterface
     */
    public function getRequest() {

        return $this->request;

    }

}
