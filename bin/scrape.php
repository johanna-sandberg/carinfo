<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../src/bootstrap.php';

use CarInfo\Http\CurlClient;
use CarInfo\Import\CarImporter;
use CarInfo\Parsing\CarParser;

final class ScrapeRunner
{
  private CurlClient $http;
  private CarParser $parser;
  private CarImporter $importer;

  public function __construct()
  {
    $this->http = new CurlClient();
    $this->parser = new CarParser();
    $this->importer = new CarImporter();
  }

  public function run(array $argv): void
  {
    $opts = $this->parseCliOptions($argv);

    $detailUrls = $this->fetchDetailUrlsFromSearchPage($opts['searchLimit']);
    fwrite(STDERR, "Collected from search: " . count($detailUrls) . "\n");

    $this->importer->begin();

    $saved = 0;
    $batchSize = 100;

    foreach ($detailUrls as $detailUrl) {
      fwrite(STDERR, "Fetch: $detailUrl\n");
      try {
        $html = $this->http->fetchHtml($detailUrl, 25, 2);
        $car = $this->parser->parseDetailPageHtml($html);
        if (!$car) {
          fwrite(STDERR, "Skip: no data\n");
          continue;
        }

        $car['source_url'] = $detailUrl;
        if (isset($car['reg_plate'])) {
          $car['reg_plate'] = strtoupper(trim($car['reg_plate']));
        }

        $this->importer->upsertCar($car);

        $saved++;
        if ($saved % $batchSize === 0) {
          $this->importer->commit();
          $this->importer->begin();
          fwrite(STDERR, "Saved: $saved\n");
        }

        usleep(random_int($opts['sleepMinMs'], $opts['sleepMaxMs']) * 1000);
      } catch (\Throwable $e) {
        fwrite(STDERR, "Item error: " . $e->getMessage() . "\n");
      }
    }

    $this->importer->commit();
    fwrite(STDERR, "Done. Total saved: $saved\n");
  }

  private function parseCliOptions(array $argv): array
  {
    $options = ['searchLimit' => 500, 'sleepMinMs' => 300, 'sleepMaxMs' => 700];
    foreach ($argv as $arg) {
      if (preg_match('~^--searchLimit=(\d+)$~', $arg, $m)) {
        $options['searchLimit'] = (int)$m[1];
      }
    }
    return $options;
  }

  private function looksLikeDetailUrl(string $url): bool
  {
    return (bool)preg_match('~^https?://bilweb\.se/.+-(\d+)$~i', $url);
  }

  private function fetchDetailUrlsFromSearchPage(int $limit): array
  {
    $limit = max(1, min(1000, $limit));
    $searchUrl = "https://bilweb.se/sok?query=&type=1&limit=" . $limit;
    fwrite(STDERR, "Search page: $searchUrl\n");

    $html = $this->http->fetchHtml($searchUrl, 20, 2);

    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xp = new \DOMXPath($dom);

    $urls = [];
    foreach ($xp->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' go_to_detail ')]") as $a) {
      if (!($a instanceof \DOMElement)) {
        continue;
      }
      $href = $a->getAttribute('href') ?? '';
      if ($href === '') {
        continue;
      }
      if (!str_starts_with($href, 'http')) {
        $href = 'https://bilweb.se' . $href;
      }
      if ($this->looksLikeDetailUrl($href)) {
        $urls[] = $href;
      }
    }

    return array_values(array_unique($urls));
  }
}

(new ScrapeRunner())->run($argv ?? []);
