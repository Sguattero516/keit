<?php

namespace BjoernGoetschke\Psr7Cookies;

spl_autoload_register(function($class) {
    if (substr($class, 0, strlen(__NAMESPACE__)+1) != __NAMESPACE__.'\\') {
        return;
    }
    $file = substr($class, strlen(__NAMESPACE__)+1);
    $file = str_replace('\\', '/', $file);
    $file = __DIR__.'/src/'.$file.'.php';
    if (is_file($file) && is_readable($file)) {
        include_once $file;
    }
});
