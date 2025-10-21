<?php

declare(strict_types=1);

(function () {
  $candidates = [
    dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env',
    getcwd() . DIRECTORY_SEPARATOR . '.env',
    __DIR__ . DIRECTORY_SEPARATOR . '.env',
  ];
  $envFile = null;
  foreach ($candidates as $p) {
    if (is_file($p) && is_readable($p)) {
      $envFile = $p;
      break;
    }
  }
  if ($envFile === null) return;

  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if ($lines === false) return;

  foreach ($lines as $line) {
    $line = ltrim($line, "\xEF\xBB\xBF");
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;

    [$k, $v] = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v);

    if ($v !== '' && ($v[0] === '"' || $v[0] === "'")) {
      $v = trim($v, "\"'");
    }

    putenv("$k=$v");
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
  }
})();

$env = static function (string $key, ?string $default = null): ?string {
  $v = getenv($key);
  if ($v !== false) return $v;
  if (array_key_exists($key, $_ENV)) return $_ENV[$key];
  if (array_key_exists($key, $_SERVER)) return $_SERVER[$key];
  return $default;
};

if (!defined('DB_DSN')) define('DB_DSN', $env('DB_DSN', 'mysql:host=127.0.0.1;dbname=carinfo;charset=utf8mb4'));
if (!defined('DB_USER')) define('DB_USER', $env('DB_USER', 'carinfo'));
if (!defined('DB_PASS')) define('DB_PASS', $env('DB_PASS', ''));
if (!defined('SCRAPER_UA')) define('SCRAPER_UA', $env('SCRAPER_UA', 'Car.Info-Assignment-Bot/1.0'));
