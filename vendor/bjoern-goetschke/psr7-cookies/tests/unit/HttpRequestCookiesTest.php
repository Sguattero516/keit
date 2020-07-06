<?php

namespace BjoernGoetschke\Test\Psr7Cookies;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;
use BjoernGoetschke\Psr7Cookies\HttpRequestCookies;

class HttpRequestCookiesTest extends PHPUnit_Framework_TestCase {

    /**
     * @param string[] $cookies
     * @return ServerRequestInterface
     */
    private static function createRequest(array $cookies) {

        $request = new ServerRequest(
            'GET',
            'http://test.domain/someUri',
            [],
            fopen('php://memory', 'rb'),
            '1.1',
            []
        );
        return $request->withCookieParams($cookies);

    }

    /**
     */
    public function testCorrectCookieValues() {

        $request = self::createRequest([
            'someCookie' => 'someValue',
            'anotherCookie' => 'anotherValue',
        ]);

        $cookies = new HttpRequestCookies($request);

        self::assertTrue(
            $cookies->hasCookie('someCookie')
        );

        self::assertTrue(
            $cookies->hasCookie('anotherCookie')
        );

        self::assertFalse(
            $cookies->hasCookie('nonExistingCookie')
        );

        self::assertSame(
            'someValue',
            $cookies->getValue('someCookie')
        );

        self::assertSame(
            'anotherValue',
            $cookies->getValue('anotherCookie')
        );

        self::assertSame(
            '',
            $cookies->getValue('nonExistingCookie')
        );

    }

    /**
     */
    public function testSameRequestReturned() {

        $request = self::createRequest([]);

        $cookies = new HttpRequestCookies($request);

        self::assertSame(
            $request,
            $cookies->getRequest()
        );

    }

    /**
     */
    public function testClone() {

        $originalRequest = self::createRequest([
            'someCookie' => 'someValue',
            'anotherCookie' => 'anotherValue',
        ]);

        $cookies1 = new HttpRequestCookies($originalRequest);
        /** @var HttpRequestCookies $cookies2 */
        $cookies2 = clone $cookies1;

        self::assertInstanceOf(
            HttpRequestCookies::class,
            $cookies2
        );

        self::assertNotSame(
            $cookies1,
            $cookies2
        );

        self::assertSame(
            $cookies1->getRequest(),
            $cookies2->getRequest()
        );

        self::assertSame(
            $cookies1->getValue('someCookie'),
            $cookies2->getValue('someCookie')
        );

        self::assertSame(
            $cookies1->getValue('anotherCookie'),
            $cookies2->getValue('anotherCookie')
        );

    }

}
