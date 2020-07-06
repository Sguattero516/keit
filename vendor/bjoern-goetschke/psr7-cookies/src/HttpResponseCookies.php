<?php

namespace BjoernGoetschke\Psr7Cookies;

use InvalidArgumentException;
use DateTime;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;

/**
 * Sets cookies in a {@see ResponseInterface}.
 *
 * @api usage
 * @copyright BSD-2-Clause, see LICENSE.txt and README.md files provided with the library source code
 */
class HttpResponseCookies {

    /**
     * The response object the cookies will be added to.
     *
     * @var ResponseInterface
     */
    private $response = null;

    /**
     * Duplicate cookies have been removed from the response.
     *
     * @var bool
     */
    private $cleaned = false;

    /**
     * Constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response) {

        $this->response = $response;

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
     * Set the specified cookie until the browser is closed.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @param string $value
     *        The value of the cookie.
     * @param string $path
     *        The path the cookie is valid for.
     * @param string $domain
     *        The domain the cookie is valid for.
     * @param bool $secure
     *        Only send the cookie using https.
     * @param bool $httpOnly
     *        Prevent the cookie to be sent using javascript.
     */
    public function setSessionCookie(
        $name,
        $value,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {

        $this->addCookieToResponse(
            $name,
            $value,
            0,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

    }

    /**
     * Set the specified cookie until the specified moment.
     *
     * If the specified moment is in the past, the cookie will be removed.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @param string $value
     *        The value of the cookie.
     * @param DateTimeImmutable $until
     *        The moment until the cookie should be valid.
     * @param string $path
     *        The path the cookie is valid for.
     * @param string $domain
     *        The domain the cookie is valid for.
     * @param bool $secure
     *        Only send the cookie using https.
     * @param bool $httpOnly
     *        Prevent the cookie to be sent using javascript.
     */
    public function setCookieUntil(
        $name,
        $value,
        DateTimeImmutable $until,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {

        $maxAge = $until->getTimestamp() - time();

        if ($maxAge < 1) {
            $maxAge = -1;
        }

        $this->addCookieToResponse(
            $name,
            $value,
            $maxAge,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

    }

    /**
     * Set the specified cookie for the specified number of seconds.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @param string $value
     *        The value of the cookie.
     * @param int $seconds
     *        The number of seconds the cookie should be valid, must be greater than or equal to zero.
     * @param string $path
     *        The path the cookie is valid for.
     * @param string $domain
     *        The domain the cookie is valid for.
     * @param bool $secure
     *        Only send the cookie using https.
     * @param bool $httpOnly
     *        Prevent the cookie to be sent using javascript.
     */
    public function setCookieFor(
        $name,
        $value,
        $seconds,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {

        $seconds = max([0, (int)$seconds]);

        $this->addCookieToResponse(
            $name,
            $value,
            $seconds,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

    }

    /**
     * Remove the specified cookie.
     *
     * @api usage
     * @param string $name
     *        The name of the cookie.
     * @param string $path
     *        The path the cookie is valid for.
     * @param string $domain
     *        The domain the cookie is valid for.
     * @param bool $secure
     *        Only send the cookie using https.
     * @param bool $httpOnly
     *        Prevent the cookie to be sent using javascript.
     */
    public function unsetCookie(
        $name,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {

        $this->addCookieToResponse(
            $name,
            '',
            -1,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

    }

    /**
     * Add the specified cookie with the specified parameters to the response.
     *
     * @param string $name
     *        The name of the cookie.
     * @param string $value
     *        The value of the cookie.
     * @param int $maxAge
     *        The maximum age of the cookie in seconds.
     * @param string $path
     *        The path the cookie is valid for.
     * @param string $domain
     *        The domain the cookie is valid for.
     * @param bool $secure
     *        Only send the cookie using https.
     * @param bool $httpOnly
     *        Prevent the cookie to be sent using javascript.
     */
    private function addCookieToResponse(
        $name,
        $value,
        $maxAge,
        $path,
        $domain,
        $secure,
        $httpOnly
    ) {

        $name = (string)$name;
        $value = (string)$value;
        $maxAge = (int)$maxAge;
        $path = (string)$path;
        $domain = (string)$domain;
        $secure = (bool)$secure;
        $httpOnly = (bool)$httpOnly;

        if (!$this->isValidCookieName($name)) {
            $msg = sprintf(
                'Invalid cookie name: %1$s',
                $name
            );
            throw new InvalidArgumentException($msg);
        }

        $cookie = [];
        $cookie[] = $name.'='.rawurlencode($value);

        if ($maxAge < 0) {
            $cookie[] = 'Expires='.gmdate(DateTime::COOKIE, 0);
            $cookie[] = 'Max-Age=0';
        } elseif ($maxAge > 0) {
            $cookie[] = 'Expires='.gmdate(DateTime::COOKIE, time() + $maxAge);
            $cookie[] = 'Max-Age='.$maxAge;
        }

        if ($path !== '') {
            $cookie[] = 'Path='.implode('/', array_map('rawurlencode', explode('/', $path)));
        }

        if ($domain !== '') {
            $cookie[] = 'Domain='.rawurlencode($domain);
        }

        if ($secure) {
            $cookie[] = 'Secure';
        }

        if ($httpOnly) {
            $cookie[] = 'HttpOnly';
        }

        $this->response = $this->response->withAddedHeader('Set-Cookie', implode(';', $cookie));

        $this->cleaned = false;

    }

    /**
     * Returns true if the specified name is a valid cookie name, otherwise false.
     *
     * @api usage
     * @param string $name
     *        The cookie name that should be checked.
     * @return bool
     */
    public function isValidCookieName($name) {

        $name = (string)$name;
        return preg_match('=^[[:ascii:]]+$=', $name) &&
               !preg_match('=[[:cntrl:][:space:]\=\,\;]+=', $name);

    }

    /**
     * Get the modified response.
     *
     * @api usage
     * @return ResponseInterface
     */
    public function getResponse() {

        if (!$this->cleaned) {
            $this->removeDuplicateCookies();
            $this->cleaned = true;
        }

        return $this->response;

    }

    /**
     * Remove duplicate cookies from the response.
     */
    private function removeDuplicateCookies() {

        $originalCookies = $this->response->getHeader('Set-Cookie');
        $cleanedCookies = [];

        foreach ($originalCookies as $cookie) {
            $name = explode('=', $cookie, 2)[0];
            $cleanedCookies[$name] = $cookie;
        }

        if (count($originalCookies) === count($cleanedCookies)) {
            return;
        }

        $this->response = $this->response->withoutHeader('Set-Cookie');
        foreach ($cleanedCookies as $cookie) {
            $this->response = $this->response->withAddedHeader('Set-Cookie', $cookie);
        }

    }

}
