CREATE TABLE IF NOT EXISTS cars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  source_url VARCHAR(512) NOT NULL UNIQUE,
  listed_at DATE NULL,

  brand VARCHAR(50) NULL,
  model VARCHAR(100) NULL,
  model_year SMALLINT UNSIGNED NULL,
  color VARCHAR(30) NULL,
  reg_plate VARCHAR(15) NULL,
  price_sek INT UNSIGNED NULL,
  mileage_km INT UNSIGNED NULL,

  horsepower SMALLINT UNSIGNED NULL,
  fuel VARCHAR(30) NULL,
  gearbox VARCHAR(30) NULL,
  body VARCHAR(50) NULL,
  location VARCHAR(100) NULL,
  dealer_name VARCHAR(120) NULL,

  title VARCHAR(255) NULL,
  description MEDIUMTEXT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT uq_source_url UNIQUE (source_url),

  INDEX idx_brand (brand),
  INDEX idx_model_year (model_year),
  INDEX idx_reg (reg_plate),
  INDEX idx_brand_year (brand, model_year),
  INDEX idx_price (price_sek),
  INDEX idx_brand_model (brand, model),

  FULLTEXT INDEX ft_title_desc (title, description),

  CHECK (model_year IS NULL OR (model_year BETWEEN 1885 AND 2100)),
  CHECK (price_sek IS NULL OR price_sek <= 999999999),
  CHECK (mileage_km IS NULL OR mileage_km <= 1000000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
