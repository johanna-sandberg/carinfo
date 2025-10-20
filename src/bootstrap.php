<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
  $prefix = 'CarInfo\\';
  $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
  if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
  $relative = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
  if (is_file($file)) require $file;
});

require __DIR__ . '/Config.php';
