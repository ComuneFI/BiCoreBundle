<?php

$request_uri = __DIR__ . $_SERVER["REQUEST_URI"];
file_put_contents("/tmp/appo.txt", $_SERVER["REQUEST_URI"]."\n", FILE_APPEND);
if (file_exists($request_uri)) {
    return false;
} else {
    file_put_contents("/tmp/appo.txt", __DIR__ . "/index.php"."\n", FILE_APPEND);
    include __DIR__ . "/index.php";
}