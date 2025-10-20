<?php

declare(strict_types=1);

namespace CarInfo\Import;

use CarInfo\Storage\Db;
use PDOStatement;

final class CarImporter
{
  private ?PDOStatement $stmt = null;
  private bool $inTx = false;
  private int $pending = 0;

  public function begin(): void
  {
    if (!$this->inTx) {
      Db::pdo()->beginTransaction();
      $this->inTx = true;
    }
  }

  public function commit(): void
  {
    if ($this->inTx) {
      Db::pdo()->commit();
      $this->inTx = false;
      $this->pending = 0;
    }
  }

  public function upsertCar(array $car): void
  {
    if ($this->stmt === null) {
      $sql = "INSERT INTO cars (
                      source_url, listed_at, manufactured_month,
                      brand, model, model_year, color, reg_plate, price_sek, mileage_km,
                      horsepower, fuel, gearbox, body, location, dealer_name,
                      title, description
                    )
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                      listed_at=COALESCE(VALUES(listed_at), listed_at),
                      manufactured_month=COALESCE(VALUES(manufactured_month), manufactured_month),
                      brand=COALESCE(VALUES(brand), brand),
                      model=COALESCE(VALUES(model), model),
                      model_year=COALESCE(VALUES(model_year), model_year),
                      color=COALESCE(VALUES(color), color),
                      reg_plate=COALESCE(VALUES(reg_plate), reg_plate),
                      price_sek=COALESCE(VALUES(price_sek), price_sek),
                      mileage_km=COALESCE(VALUES(mileage_km), mileage_km),
                      horsepower=COALESCE(VALUES(horsepower), horsepower),
                      fuel=COALESCE(VALUES(fuel), fuel),
                      gearbox=COALESCE(VALUES(gearbox), gearbox),
                      body=COALESCE(VALUES(body), body),
                      location=COALESCE(VALUES(location), location),
                      dealer_name=COALESCE(VALUES(dealer_name), dealer_name),
                      title=COALESCE(VALUES(title), title),
                      description=COALESCE(VALUES(description), description)";
      $this->stmt = Db::pdo()->prepare($sql);
    }

    $this->stmt->execute([
      $car['source_url'],
      $car['listed_at'] ?? null,
      $car['manufactured_month'] ?? null,

      $car['brand'] ?? null,
      $car['model'] ?? null,
      $car['model_year'] ?? null,
      $car['color'] ?? null,
      $car['reg_plate'] ?? null,
      $car['price_sek'] ?? null,
      $car['mileage_km'] ?? null,

      $car['horsepower'] ?? null,
      $car['fuel'] ?? null,
      $car['gearbox'] ?? null,
      $car['body'] ?? null,
      $car['location'] ?? null,
      $car['dealer_name'] ?? null,

      $car['title'] ?? null,
      $car['description'] ?? null
    ]);

    if ($this->inTx) {
      $this->pending++;
    }
  }
}
