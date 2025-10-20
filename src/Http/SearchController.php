<?php

declare(strict_types=1);

namespace CarInfo\Http;

use CarInfo\Storage\Db;
use PDO;

final class SearchController
{
  private PDO $pdo;

  public function __construct()
  {
    $this->pdo = Db::pdo();
  }

  public function handle(array $query): array
  {
    $brand = trim((string)($query['brand'] ?? ''));
    $modelYear = trim((string)($query['model_year'] ?? ''));
    $regPrefix = strtoupper(trim((string)($query['reg'] ?? '')));
    $limit = max(1, min(100, (int)($query['limit'] ?? 25)));
    $offset = max(0, (int)($query['offset'] ?? 0));

    $where = [];
    $params = [];

    if ($brand !== '') {
      $where[] = 'brand LIKE :brand';
      $params[':brand'] = $brand . '%';
    }

    if ($modelYear !== '' && ctype_digit($modelYear)) {
      $where[] = 'model_year = :model_year';
      $params[':model_year'] = (int)$modelYear;
    }

    if ($regPrefix !== '') {
      $where[] = 'reg_plate LIKE :reg';
      $params[':reg'] = $regPrefix . '%';
    }

    $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
            SELECT
              source_url,
              title,
              brand,
              model,
              model_year,
              reg_plate,
              mileage_km,
              price_sek,
              fuel,
              gearbox,
              body
            FROM cars
            $sqlWhere
            ORDER BY updated_at DESC, id DESC
            LIMIT :limit OFFSET :offset
        ";

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    return [
      'items' => $items,
      'limit' => $limit,
      'offset' => $offset,
      'count' => count($items),
      'timestamp' => date('c'),
    ];
  }
}
