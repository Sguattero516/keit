<?php

chdir(__DIR__.'/vendor/');

$remove = [
    'doctrine/*/tests/',
    'phpunit/*/build/',
    'phpunit/*/tests/',
    'sebastian/*/build/',
    'sebastian/*/tests/',
    'squizlabs/php_codesniffer/scripts/',
    'squizlabs/php_codesniffer/tests/',
    'squizlabs/php_codesniffer/src/Standards/*/Docs/',
    'squizlabs/php_codesniffer/src/Standards/*/Tests/',
];

$remove_match = function($match) use (&$remove_match) {
    if (mb_strpos(realpath($match), realpath(getcwd())) !== 0) {
        echo '  ! WILL NOT REMOVE FILE OUTSIDE OF WORKING DIRECTORY: '.$match.PHP_EOL;
        return false;
    }
    if (!file_exists($match)) {
        return false;
    }
    if (!is_dir($match)) {
        return unlink($match);
    }
    $subMatches = glob($match.'{.,}[!.,!..]*', GLOB_BRACE|GLOB_MARK|GLOB_NOSORT);
    foreach ($subMatches as $subMatch) {
        $remove_match($subMatch);
    }
    return rmdir($match);
};

echo 'Removing not needed files from vendor directory'.PHP_EOL;
foreach ($remove as $glob) {
    foreach (glob($glob, GLOB_MARK|GLOB_NOSORT) as $match) {
        echo '  - '.$match.PHP_EOL;
        $remove_match($match);
    }
}
