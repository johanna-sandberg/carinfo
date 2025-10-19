<?php

require __DIR__.'/../src/Db.php';

try {
  $pdo = Db::pdo();
  $n = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
  echo "Database connection successful. There are {$n} records in the 'cars' table.";
} catch (Throwable $e) {
  http_response_code(500);
  echo "Database connection failed: " . htmlspecialchars($e->getMessage()); 
}
