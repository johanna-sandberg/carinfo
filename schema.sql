CREATE TABLE IF NOT EXISTS cars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  source_url VARCHAR(512) NOT NULL UNIQUE,
  listed_at DATE NULL,
  manufactured_month CHAR(7) NULL,

  brand VARCHAR(100) NULL,
  model VARCHAR(150) NULL,
  model_year SMALLINT UNSIGNED NULL,
  color VARCHAR(50) NULL,
  reg_plate VARCHAR(20) NULL,
  price_sek INT UNSIGNED NULL,
  mileage_km INT UNSIGNED NULL,

  horsepower SMALLINT UNSIGNED NULL,
  fuel VARCHAR(50) NULL,
  gearbox VARCHAR(50) NULL,
  body VARCHAR(50) NULL,
  location VARCHAR(100) NULL,
  dealer_name VARCHAR(255) NULL,

  title VARCHAR(255) NULL,
  description MEDIUMTEXT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT uq_source_url UNIQUE (source_url),

  INDEX idx_brand (brand),
  INDEX idx_model (model),
  INDEX idx_model_year (model_year),
  INDEX idx_location (location),
  INDEX idx_reg (reg_plate),
  INDEX idx_fuel (fuel),
  INDEX idx_gearbox (gearbox),
  INDEX idx_body (body),
  INDEX idx_price (price_sek),
  INDEX idx_mileage (mileage_km),
  INDEX idx_manufactured_month (manufactured_month),
  INDEX idx_brand_year (brand, model_year),
  INDEX idx_brand_model (brand, model),

  FULLTEXT INDEX ft_title_desc (title, description),

  CHECK (model_year IS NULL OR (model_year BETWEEN 1885 AND 2100)),
  CHECK (price_sek IS NULL OR price_sek <= 999999999),
  CHECK (mileage_km IS NULL OR mileage_km <= 1000000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
