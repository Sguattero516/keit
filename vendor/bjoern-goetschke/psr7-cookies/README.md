# PSR7 cookie handling library

This library provides the class `BjoernGoetschke\Psr7Cookies\HttpRequestCookies` to read cookies from a
`Psr\Http\Message\ServerRequestInterface` request and the class `BjoernGoetschke\Psr7Cookies\HttpResponseCookies`
to modify the cookies that will be sent with a `Psr\Http\Message\ResponseInterface` response.

## Basic usage

Applications can easily read cookie values:

    /** @var \Psr\Http\Message\ServerRequestInterface $request */
    $cookies = new \BjoernGoetschke\Psr7Cookies\HttpRequestCookies($request);
    if ($cookies->hasCookie('someCookie')) {
        $cookieValue = $cookies->getValue('someCookie');
    }

It is similarly easy to modify the cookies of a response:

    /** @var \Psr\Http\Message\ResponseInterface $response */
    $cookies = \BjoernGoetschke\Psr7Cookies\HttpResponseCookies($response);
    // set some cookie until the browser is closed
    $cookies->setSessionCookie('someCookie', 'someValue');
    // set another cookie for 1 hour (3600 seconds)
    $cookies->setCookieFor('anotherCookie', 'anotherValue', 3600);
    // delete an existing cookie
    $cookies->unsetCookie('existingCookie');
    // retrieve the modified response
    $response = $cookies->getResponse();

## Installation

The library is available via Composer:

    composer require bjoern-goetschke/psr7-cookies:^1.0

## Versioning

Releases will be numbered with the following format using semantic versioning:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backwards compatibility bumps the major
* New additions without breaking backwards compatibility bumps the minor
* Bug fixes and misc changes bump the patch

For more information on semantic versioning, please visit http://semver.org/.

## LICENSE

The library is released under the BSD-2-Clause license. You can find a copy of this license in LICENSE.txt.

## API usage and backwards compatibility

Information about the intended usage of interfaces, classes, methods, etc. is specified with the `@api` tag.

If an element does not contain the `@api` tag it should be considered internal and usage may break at any time.

One exception to this rule are special elements like constructors, destructors or other hook methods that are defined
by the programming language. These elements will not have their own `@api` tag but can be considered as if they have
the same `@api` tag as the class or whatever other element they belong to.

### `@api usage`

* Classes
    * Create new instances of the class
        * may break on `major`-releases
    * Extending the class and adding a new constructor
        * may break on `major`-releases
    * Extending the class and adding new methods
        * may break at any time, but `minor`-releases should be ok most of the time
            (will break if a non-private method has been added to the base class that was also declared
            in the extending class)
* Methods
    * Calling the method
        * may break on `major`-releases
    * Overriding the method (extending the class and declaring a method with the same name) and eventually
        adding additional optional arguments
        * may break at any time, but `minor`-releases should be ok most of the time
            (will break if an optional argument has been added to the method in the base-class)
* Interfaces
    * Using the interface in type-hints (require an instance of the interface as argument)
        * may break on `major`-releases
    * Calling methods of the interface
        * may break on `major`-releases
    * Implementing the interface
        * may break at any time, but `minor`-releases should be ok most of the time
            (will break if new methods have been added to the interface)
    * Extending the interface
        * may break at any time, but `minor`-releases should be ok most of the time
            (will break if a method has been added to the base interface that was also declared
            in the extending interface)

### `@api extend`

* Classes
    * Create new instances of the class
        * may break on `major`-releases
    * Extending the class and adding a new constructor
        * may break on `major`-releases
    * Extending the class and adding new methods
        * may break on `minor`-releases, but it should be ok most of the time and may only break on `major`-releases
            (will break if a non-private method has been added to the base class that was also declared in the
            extending class)
* Methods
    * Calling the method
        * may break on `major`-releases
    * Overriding the method (extending the class and declaring a method with the same name) and eventually
        adding additional optional arguments
        * may break on `major`-releases
* Interfaces
    * Using the interface in type-hints (require an instance of the interface as argument)
        * may break on `major`-releases
    * Calling methods of the interface
        * may break on `major`-releases
    * Implementing the interface
        * may break on `major`-releases
    * Extending the interface
        * may break on `major`-releases

### `@api stable`

* Everything that is marked as stable may only break on `major`-releases, this means that except some minor internal
    changes or bugfixes the code will just never change at all

### `@api internal`

* Everything that is marked as internal may break at any time, but `patch`-releases should be ok most of the time
