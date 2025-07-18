<?php
function env($key, $default = null) {
    static $env;
    if (!$env) {
        $path = __DIR__ . '/../.env';
        if (file_exists($path)) {
            $env = parse_ini_file($path);
        } else {
            $env = [];
        }
    }
    return $env[$key] ?? $default;
}