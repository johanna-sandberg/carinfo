<?php

define('DB_DSN', getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=carinfo;charset=utf8mb4');
define('DB_USER', getenv('DB_USER') ?: 'carinfo');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('SCRAPER_UA', getenv('SCRAPER_UA') ?: 'Car.Info-Assignment-Bot/1.0 (contact: johanna_sandberg@outlook.com)');
