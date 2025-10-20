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

    $pdo = new PDO(\DB_DSN, \DB_USER, \DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_PERSISTENT         => true,
      PDO::ATTR_EMULATE_PREPARES   => true,
    ]);
    $pdo->exec('SET NAMES utf8mb4');
    return $pdo;
  }
}
