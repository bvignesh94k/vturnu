<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== '') {
    return false;
}
if (is_dir($file) && is_file($file . '/index.php')) {
    return false;
}
require __DIR__ . '/index.php';
