<?php
require_once __DIR__.'/Db.php';

function saveCarData(array $carData): void {
  $sql = "INSERT INTO cars (source_url,brand,model,model_year,reg_plate,price_sek,
          mileage_km,fuel,gearbox,body,location,listed_at,title,description)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
          brand=VALUES(brand), model=VALUES(model), model_year=VALUES(model_year),
          reg_plate=VALUES(reg_plate), price_sek=VALUES(price_sek),
          mileage_km=VALUES(mileage_km), fuel=VALUES(fuel), gearbox=VALUES(gearbox),
          body=VALUES(body), location=VALUES(location), listed_at=VALUES(listed_at),
          title=VALUES(title), description=VALUES(description)";
  Db::pdo()->prepare($sql)->execute([
    $carData['source_url'],
    $carData['brand'] ?? null,
    $carData['model'] ?? null,
    $carData['model_year'] ?? null,
    $carData['reg_plate'] ?? null,
    $carData['price_sek'] ?? null,
    $carData['mileage_km'] ?? null,
    $carData['fuel'] ?? null,
    $carData['gearbox'] ?? null,
    $carData['body'] ?? null,
    $carData['location'] ?? null,
    $carData['listed_at'] ?? null,
    $carData['title'] ?? null,
    $carData['description'] ?? null
  ]);
}
