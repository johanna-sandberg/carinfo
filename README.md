# Car.info PHP Assignment

## Requirements

* PHP 8.2+ with `curl`, `pdo_mysql`, `mbstring`
* MySQL

## Project Structure

```
bin/
  scrape.php
public/
  api/
    search.php
  app.js
  db_check.php
  index.php
  info.php
src/
  Config.php
  Db.php
  Http.php
  Importer.php
  Parser.php
schema.sql
README.md
.gitattributes
.gitignore
```

## Run locally

```bash
php -S 127.0.0.1:8080 -t public
```

Open: [http://127.0.0.1:8080](http://127.0.0.1:8080)

## Test search

1. Fill in at least one field (Brand/Model Year/Reg prefix).
2. Click **Search**.
3. The result panel will display JSON with the received parameters.

Example using curl:

```bash
curl "http://127.0.0.1:8080/api/search.php?brand=Volvo&model_year=2018&limit=25&offset=0"
```

Example response:

```json
{
  "received": {
    "brand": "Volvo",
    "model_year": "2018",
    "reg": "",
    "limit": 25,
    "offset": 0
  },
  "timestamp": "2025-10-19T12:34:56+00:00"
}
```

## Pager logic

* Hidden before the first search.
* **Previous** appears from page 2 (offset ≥ limit).
* **Next** only appears if there are more pages (hidden if lastCount < limit).
