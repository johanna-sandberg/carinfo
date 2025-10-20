<?php

declare(strict_types=1);

namespace CarInfo\Storage;

use PDO;

final class Db
{
  public static function pdo(): PDO
  {
    static $pdo = null;
    if ($pdo instanceof PDO) {
      return $pdo;
    }

    if (!defined('DB_DSN')) {
      require_once __DIR__ . '/../Config.php';
    }

    $dsn = DB_DSN;
    if (stripos($dsn, 'mysql:') === 0 && strpos($dsn, 'charset=') === false) {
      $dsn .= (strpos($dsn, ';') === false ? ';' : '') . 'charset=utf8mb4';
    }

    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    return $pdo;
  }
}
