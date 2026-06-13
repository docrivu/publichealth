<?php

declare(strict_types=1);

session_start();

$configFile = __DIR__ . '/../config/app.php';
$exampleConfigFile = __DIR__ . '/../config/app.example.php';
$config = file_exists($configFile) ? require $configFile : require $exampleConfigFile;

function config(string $key, mixed $default = null): mixed
{
    global $config;

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cga_schema.php';
