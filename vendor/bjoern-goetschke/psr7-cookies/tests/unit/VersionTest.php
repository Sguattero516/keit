<?php

namespace BjoernGoetschke\Test\Psr7Cookies;

use PHPUnit_Framework_TestCase;
use BjoernGoetschke\Psr7Cookies\Version;

class VersionTest extends PHPUnit_Framework_TestCase {

    /**
     */
    public function testCompareVersionOlder() {

        $testVersion = '0.'.Version::VERSION;
        $actual = Version::compareVersion($testVersion);
        $expected = -1;

        self::assertEquals($expected, $actual);

    }

    /**
     */
    public function testCompareVersionEqual() {

        $testVersion = Version::VERSION;
        $actual = Version::compareVersion($testVersion);
        $expected = 0;

        self::assertEquals($expected, $actual);

    }

    /**
     */
    public function testCompareVersionNewer() {

        $testVersion = Version::VERSION.'.1';
        $actual = Version::compareVersion($testVersion);
        $expected = 1;

        self::assertEquals($expected, $actual);

    }

}
