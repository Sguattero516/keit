<?php

namespace BjoernGoetschke\Psr7Cookies;

/**
 * Provides methods to check the library version.
 *
 * @api stable
 * @copyright BSD-2-Clause, see LICENSE.txt and README.md files provided with the library source code
 */
final class Version {

    /**
     * Library version.
     *
     * @api stable
     * @var string
     */
    const VERSION = '1.0.6';

    /**
     * Private constructor.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {
    }

    /**
     * Compare the specified version with the current version of the library.
     *
     * Compare the specified version string $version with the current value of {@see Version::VERSION}.
     *
     * Return value will be -1 if $version is older, 0 if they are the same and +1 if $version is newer.
     *
     * @api stable
     * @param string $version
     *        The version string that should be compared to the current version of the library.
     * @return int
     */
    public static function compareVersion($version) {

        $version = mb_strtolower($version);
        return version_compare($version, mb_strtolower(self::VERSION));

    }

}
