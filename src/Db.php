<?php

final class Db {
  public static function pdo(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    require __DIR__.'/Config.php';
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
    return $pdo;
  }
}
