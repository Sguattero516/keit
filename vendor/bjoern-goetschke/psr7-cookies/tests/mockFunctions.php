<?php

namespace {

    global $mock_time;
    $mock_time = null;
    function mock_time() {
        global $mock_time;
        $function = $mock_time;
        if (!is_callable($function)) {
            $function = '\time';
        }
        return $function();
    }

    function resetMocks() {
        global $mock_time;
        $mock_time = null;
    }

}

namespace BjoernGoetschke\Psr7Cookies {

    function time() {
        return \mock_time();
    }

}
