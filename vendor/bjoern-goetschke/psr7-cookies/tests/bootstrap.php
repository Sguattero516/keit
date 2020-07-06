<?php

// ===== set default php settings =====

ini_set('error_reporting', E_ALL|E_STRICT);
ini_set('display_errors', true);
ini_set('html_errors', false);
date_default_timezone_set('UTC');
mb_detect_order(['UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'CP1252', 'CP1251']);
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

// ===== check php settings =====

if (ini_get('magic_quotes_gpc') || ini_get('magic_quotes_runtime')) {
    trigger_error(
        'Unable to operate with magic quotes enabled.',
        E_USER_ERROR
    );
}

if (ini_get('session.auto_start')) {
    trigger_error(
        'Unable to operate with session autostart enabled.',
        E_USER_ERROR
    );
}

// ===== initialize autoloader =====

require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/mockFunctions.php';

// ===== include local bootstrap script =====

@include __DIR__.'/bootstrap.local.php';
