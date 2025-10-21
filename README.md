# Car.info PHP Project Assignment

Fullstack PHP assignment that includes:
* A **web scraper** for collecting car listings from [bilweb.se](https://bilweb.se)
* A **search frontend** with a simple car browser (JSON API + Bootstrap UI)

---

## Live Demo

Hosted on **InfinityFree**:  
[https://carinfo.infinityfree.me](https://carinfo.infinityfree.me)

---

## Requirements

- PHP **8.2+**
  - Extensions: `curl`, `pdo_mysql`, `mbstring`
- MySQL **8.x** (or compatible)
- Browser with JavaScript enabled

---

## Project Structure

```

carinfo/
├── bin/
│ └── scrape.php    # CLI scraper that fetches and 
│                     imports car data from bilweb.se
│
├── public/
│ ├── api/
│ │ └── search.php  # JSON API endpoint for the search
│                     frontend
│ │
│ ├── app.js        # Frontend logic for the search 
│                     form, pagination, and rendering
│ ├── index.php     # Main search page (Bootstrap layout)
│ ├── style.css     # Custom dark/light mode styling
│ └── theme.js      # Theme switcher logic 
│                     (dark/light toggle)
│
├── src/
│ ├── Http/
│ │ ├── CurlClient.php       # Lightweight HTTP client 
│ │ │                          used by scraper
│ │ └── SearchController.php # Handles API requests for 
│ │                            car search
│ │
│ ├── Import/
│ │ └── CarImporter.php   # Inserts or updates car rows 
│ │                         in database (upsert)
│ │
│ ├── Parsing/
│ │ └── CarParser.php # Extracts structured car data 
│ │                     from bilweb.se HTML
│ │
│ ├── Storage/
│ │ └── Db.php        # Central PDO connection factory
│ │
│ ├── bootstrap.php   # Autoloader + environment bootstrap
│ └── Config.php      # Loads .env configuration and 
│                       defines constants
│
├── .env.example      # Example environment file with 
│                       DB credentials
├── .gitattributes    # Git normalization rules
├── .gitignore        # Files and folders to exclude from Git
├── README.md         # Project documentation
└── schema.sql        # MySQL schema for the cars table

````

---

## Configuration

Copy `.env.example` to `.env` and adjust for your local or hosted database:

```bash
DB_DSN="mysql:host=127.0.0.1;dbname=carinfo;charset=utf8mb4"
DB_USER="carinfo"
DB_PASS="password"
SCRAPER_UA="Car.Info-Assignment-Bot/1.0 (contact: johanna_sandberg@outlook.com)"
````

On **InfinityFree**, `.env` uses the provided credentials from your control panel.

---

## Database Setup

Run the SQL from `schema.sql` in phpMyAdmin or the MySQL CLI:

```bash
mysql -u carinfo -p carinfo < schema.sql
```

This creates the `cars` table with indexes and unique keys for `external_id` and `source_url`.

---

## Running the Scraper

The scraper fetches car listings directly from Bilweb and imports them into MySQL.

```bash
php bin/scrape.php --searchLimit=800 --batch=50
```

**Options:**

* `--searchLimit=N` → how many listings to fetch (max 1000)
* `--batch=N` → number of rows to commit per batch (default: 50)

**Example Output:**

```
Search page: https://bilweb.se/sok?query=&type=1&limit=800
Collected from search: 800
Committed batch, total saved: 50
Committed batch, total saved: 100
Done. Total saved: 740
```

Each unique listing (based on Bilweb’s internal ID) is inserted or updated (no duplicates).

> **Note:** The scraper must be run **locally** or on a server where you have terminal access.
> Free hosts like InfinityFree do not allow CLI or background jobs, so you’ll need to:
>
> 1. Run the scraper **locally** (it saves data in your local MySQL database).
> 2. Export your local `cars` table from phpMyAdmin (`Export → SQL`).
> 3. Import that SQL file into your **InfinityFree** database via its phpMyAdmin.
> 4. The hosted search page will then display the new data.
---

## Running the Search Frontend Locally

```bash
php -S 127.0.0.1:8080 -t public
```

Then open:
[http://127.0.0.1:8080](http://127.0.0.1:8080)

The app will automatically load data from your local database through the `/api/search.php` endpoint.

---

## Features

* **Scraper:** Parses Bilweb HTML, extracts structured car data, and stores/upserts in MySQL.
* **Search API:** Returns paginated, filtered car results in JSON.
* **Frontend:** Simple Bootstrap UI with form-based filtering and dark/light theme toggle.
* **Auto-detect theme:** Uses OS preference by default (`prefers-color-scheme`), can be toggled manually.
* **Responsive layout:** Works across mobile and desktop.

---

## Deployment Notes

* Public files go under `/htdocs` on InfinityFree.
* `src/`, `.env`, and other backend files live **outside** `/htdocs`.
* `.htaccess` in `/htdocs` should only contain:

  ```apache
  Options -Indexes
  <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
  </IfModule>
  ```
* **You cannot run the scraper on InfinityFree** — run it locally and import data into the hosted DB.
---

## API Example

```bash
curl "https://carinfo.infinityfree.me/api/search.php?brand=Volvo&model_year=2018&limit=10"
```

**Response:**

```json
{
  "items": [
    {
      "brand": "Volvo",
      "model": "V60",
      "model_year": 2018,
      "price_sek": 189000,
      "mileage_km": 12500,
      "source_url": "https://bilweb.se/...-12210001"
    }
  ],
  "total": 240,
  "limit": 10,
  "offset": 0
}
```

---

## Maintenance

To reset data:

```sql
TRUNCATE TABLE cars;
```

Then rerun the scraper.

---

## Version

**Current version:** October 2025
Author: *Johanna Sandberg*
Hosted at: [carinfo.infinityfree.me](https://carinfo.infinityfree.me)
