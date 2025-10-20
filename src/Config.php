<?php

(function () {
  $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
  if (!is_file($envFile) || !is_readable($envFile)) return;
  foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v);
    if ($v !== '' && ($v[0] === '"' || $v[0] === "'")) $v = trim($v, "\"'");

    if (getenv($k) === false) {
      putenv("$k=$v");
      $_ENV[$k] = $v;
      $_SERVER[$k] = $v;
    }
  }
})();

if (!defined('DB_DSN')) define('DB_DSN', getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=carinfo;charset=utf8mb4');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'carinfo');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'CarInfoAssignment2025');
if (!defined('SCRAPER_UA')) define('SCRAPER_UA', getenv('SCRAPER_UA') ?: 'Car.Info-Assignment-Bot/1.0 (contact: johanna_sandberg@outlook.com)');
