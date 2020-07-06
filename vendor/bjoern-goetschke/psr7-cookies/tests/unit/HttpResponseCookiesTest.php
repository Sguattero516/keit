<?php

namespace BjoernGoetschke\Test\Psr7Cookies;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use DateTime;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use BjoernGoetschke\Psr7Cookies\HttpResponseCookies;

class HttpResponseCookiesTest extends PHPUnit_Framework_TestCase {

    /**
     * {@inheritdoc}
     */
    public function setUp() {

        parent::setUp();
        resetMocks();

    }

    /**
     * {@inheritdoc}
     */
    public function tearDown() {

        parent::tearDown();
        resetMocks();

    }

    /**
     * @return ResponseInterface
     */
    private static function createResponse() {

        return new Response(204);

    }

    /**
     * @param ResponseInterface $response
     * @return mixed[]
     */
    private static function extractHeaders(ResponseInterface $response) {

        return array_map(
            function($header) {
                return explode(';', $header);
            },
            $response->getHeader('Set-Cookie')
        );

    }

    /**
     */
    public function testSetSessionCookie() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setSessionCookie('someCookie', 'someValue', '/somePath', 'some.domain', true, true);

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieUntil() {

        global $mock_time;
        $mock_time = function() {
            return 10000000;
        };

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieUntil(
            'someCookie',
            'someValue',
            new DateTimeImmutable('@12345678'),
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Expires='.gmdate(DateTime::COOKIE, 12345678),
                    'Max-Age=2345678',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieUntilNow() {

        global $mock_time;
        $mock_time = function() {
            return 12345678;
        };

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieUntil(
            'someCookie',
            'someValue',
            new DateTimeImmutable('@12345678'),
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Expires='.gmdate(DateTime::COOKIE, 0),
                    'Max-Age=0',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieUntilInThePast() {

        global $mock_time;
        $mock_time = function() {
            return 123456789;
        };

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieUntil(
            'someCookie',
            'someValue',
            new DateTimeImmutable('@12345678'),
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Expires='.gmdate(DateTime::COOKIE, 0),
                    'Max-Age=0',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieFor() {

        global $mock_time;
        $mock_time = function() {
            return 10000000;
        };

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieFor(
            'someCookie',
            'someValue',
            2345678,
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Expires='.gmdate(DateTime::COOKIE, 12345678),
                    'Max-Age=2345678',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieForZeroSeconds() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieFor(
            'someCookie',
            'someValue',
            0,
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testSetCookieForNegativeSeconds() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setCookieFor(
            'someCookie',
            'someValue',
            -1,
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=someValue',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testUnsetCookie() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->unsetCookie(
            'someCookie',
            '/somePath',
            'some.domain',
            true,
            true
        );

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            1,
            $headers
        );

        self::assertSame(
            [
                [
                    'someCookie=',
                    'Expires='.gmdate(DateTime::COOKIE, 0),
                    'Max-Age=0',
                    'Path=/somePath',
                    'Domain=some.domain',
                    'Secure',
                    'HttpOnly',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testInvalidCookieNameThrowsException() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $this->setExpectedException(
            InvalidArgumentException::class,
            'Invalid cookie name: name=invalid'
        );

        $cookies->setSessionCookie('name=invalid', 'someValue');

    }

    /**
     */
    public function testDuplicateCookiesAreReplaced() {

        $cookies = new HttpResponseCookies(self::createResponse());

        $cookies->setSessionCookie('cookie1', 'value1'); // will be replaced
        $cookies->setSessionCookie('cookie2', 'value2'); // final
        $cookies->setSessionCookie('cookie3', 'value3'); // will be replaced
        $cookies->setSessionCookie('cookie1', 'value1.1'); // final
        $cookies->unsetCookie('cookie3'); // final
        $cookies->unsetCookie('cookie4'); // will be replaced
        $cookies->setSessionCookie('cookie4', 'value4'); // final

        $headers = self::extractHeaders($cookies->getResponse());

        self::assertCount(
            4,
            $headers
        );

        self::assertSame(
            [
                [
                    'cookie1=value1.1',
                ],
                [
                    'cookie2=value2',
                ],
                [
                    'cookie3=',
                    'Expires='.gmdate(DateTime::COOKIE, 0),
                    'Max-Age=0'
                ],
                [
                    'cookie4=value4',
                ],
            ],
            $headers
        );

    }

    /**
     */
    public function testClone() {

        $cookies1 = new HttpResponseCookies(self::createResponse());
        /** @var HttpResponseCookies $cookies2 */
        $cookies2 = clone $cookies1;

        self::assertInstanceOf(
            HttpResponseCookies::class,
            $cookies2
        );

        self::assertNotSame(
            $cookies1,
            $cookies2
        );

        self::assertSame(
            $cookies1->getResponse(),
            $cookies2->getResponse()
        );

    }

}
