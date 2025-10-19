<?php
header('Content-Type: application/json; charset=utf-8');

$params = [
  'brand' => trim($_GET['brand'] ?? ''),
  'model_year' => trim($_GET['model_year'] ?? ''),
  'reg' => trim($_GET['reg'] ?? ''),
  'limit' => (int)($_GET['limit'] ?? 25),
  'offset' => (int)($_GET['offset'] ?? 0),
];

echo json_encode([
  'received' => $params,
  'timestamp' => date('c'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
