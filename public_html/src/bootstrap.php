<?php

declare(strict_types=1);

session_start();

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$host = preg_replace('/:\d+$/', '', $host) ?: '';

$configFile = __DIR__ . '/../config/app.php';
if ($host !== '') {
    $hostConfigFile = __DIR__ . '/../config/app.' . preg_replace('/[^a-z0-9.-]/', '', $host) . '.php';
    if (file_exists($hostConfigFile)) {
        $configFile = $hostConfigFile;
    }
}
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
require_once __DIR__ . '/survey_schema.php';
