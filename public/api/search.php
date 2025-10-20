<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../../src/bootstrap.php';

use CarInfo\Http\SearchController;

$controller = new SearchController();
$response = $controller->handle($_GET);

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
