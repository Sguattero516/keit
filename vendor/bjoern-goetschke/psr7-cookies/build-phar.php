<?php

if (!Phar::canWrite()) {
    trigger_error(
        'Unable to operate with phar files set to readonly.',
        E_USER_ERROR
    );
}

chdir(__DIR__);

$config = json_decode(file_get_contents(__DIR__.'/composer.json'));
$file = __DIR__.'/'.str_replace('/', '.', $config->name).'.phar';

if (file_exists($file)) {
    unlink($file);
}

$addFile = function(Phar $phar, $file) {
    $phar->addFile($file);
};
$addDir = function(Phar $phar, $dir) {
    $phar->buildFromIterator(
        new CallbackFilterIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)),
            function ($current) {
                return is_file($current);
            }
        ),
        __DIR__
    );
};

$phar = new Phar($file);
$addFile($phar, 'composer.json');
$addFile($phar, 'LICENSE.txt');
$addFile($phar, 'autoload.php');
$addDir($phar, 'src/');
$phar->setStub("<?php
Phar::mapPhar('self.phar');
if (count(debug_backtrace()) < 1) {
    header('HTTP/1.1 500 Internal Server Error');
    exit(255);
}
require 'phar://self.phar/autoload.php';
__HALT_COMPILER();");

if (!file_exists($file)) {
    trigger_error(
        'Phar file not written.',
        E_USER_ERROR
    );
}

echo 'Build: '.$file.PHP_EOL;
require $file;
echo 'Version: '.\BjoernGoetschke\Psr7Cookies\Version::VERSION.PHP_EOL;
