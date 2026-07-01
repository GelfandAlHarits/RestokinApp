<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $e = error_get_last();

    if ($e) {
        file_put_contents(
            '/tmp/php-fatal.log',
            print_r($e, true),
            FILE_APPEND
        );
    }
});

echo "Reached api/index.php\n";
flush();

require __DIR__.'/../public/index.php';