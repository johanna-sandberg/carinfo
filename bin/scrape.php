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

    $cards = $this->fetchCardsFromSearch($opts['searchLimit']);
    fwrite(STDERR, "Collected from search: " . count($cards) . "\n");

    $this->importer->begin();

    $saved = 0;
    $batchSize = $opts['batch'];

    foreach ($cards as $externalId => $detailUrl) {
      fwrite(STDERR, "Fetch: $detailUrl\n");
      try {
        $html = $this->http->fetchHtml($detailUrl, 25, 2);
        $car = $this->parser->parseDetailPageHtml($html);
        if (!$car) {
          fwrite(STDERR, "Skip: no data\n");
          continue;
        }

        $car['external_id'] = (string)$externalId;
        $car['source_url'] = $detailUrl;
        if (isset($car['reg_plate'])) $car['reg_plate'] = strtoupper(trim($car['reg_plate']));

        $this->importer->upsertCar($car);

        $saved++;
        if ($saved % $batchSize === 0) {
          $this->importer->commit();
          $this->importer->begin();
          fwrite(STDERR, "Committed batch, total saved: $saved\n");
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
    $options = ['searchLimit' => 800, 'batch' => 50, 'sleepMinMs' => 300, 'sleepMaxMs' => 700];
    foreach ($argv as $arg) {
      if (preg_match('~^--searchLimit=(\d+)$~', $arg, $m)) $options['searchLimit'] = (int)$m[1];
      elseif (preg_match('~^--batch=(\d+)$~', $arg, $m)) $options['batch'] = max(1, (int)$m[1]);
    }
    return $options;
  }

  private function fetchCardsFromSearch(int $limit): array
  {
    $limit = max(1, min(1000, $limit));
    $url = "https://bilweb.se/sok?query=&type=1&limit=" . $limit;
    fwrite(STDERR, "Search page: $url\n");

    $html = $this->http->fetchHtml($url, 20, 2);

    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xp = new \DOMXPath($dom);

    $map = [];

    foreach ($xp->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' Card ')][@id]") as $card) {
      if (!($card instanceof \DOMElement)) continue;
      $idAttr = trim($card->getAttribute('id') ?? '');
      if ($idAttr === '' || !ctype_digit($idAttr)) continue;

      $a = $xp->query(".//a[contains(concat(' ', normalize-space(@class), ' '), ' go_to_detail ')]", $card)->item(0);
      if (!($a instanceof \DOMElement)) continue;

      $href = trim($a->getAttribute('href') ?? '');
      if ($href === '') continue;
      if (!str_starts_with($href, 'http')) $href = 'https://bilweb.se' . $href;

      $map[(int)$idAttr] = $href;
    }

    if (!$map) {
      foreach ($xp->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' go_to_detail ')]") as $a) {
        if (!($a instanceof \DOMElement)) continue;
        $href = trim($a->getAttribute('href') ?? '');
        if ($href === '') continue;
        if (!str_starts_with($href, 'http')) $href = 'https://bilweb.se' . $href;
        if (preg_match('~-(\d+)$~', $href, $m)) $map[(int)$m[1]] = $href;
      }
    }

    return $map;
  }
}

(new ScrapeRunner())->run($argv ?? []);
