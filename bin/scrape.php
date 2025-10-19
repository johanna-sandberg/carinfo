<?php

require __DIR__ . '/../src/Db.php';
require __DIR__ . '/../src/Http.php';
require __DIR__ . '/../src/Importer.php';
require __DIR__ . '/../src/Parser.php';

function getUrlsFromSitemapIndex(int $pageStart, int $count, int $target=600): array {
  $urls = [];
  for ($i = $pageStart; $i < $pageStart + $count; $i++) {
    $sitemapIndexUrl = "https://bilweb.se/sitemap/vehicles/$i.xml";
    $xml = curl_get($sitemapIndexUrl);
    preg_match_all('~https://bilweb\.se/[^\s<]+~', $xml, $m);
    foreach ($m[0] as $u) $urls[] = $u;
    $urls = array_values(array_unique($urls));
    if (count($urls) >= $target) break;
    usleep(random_int(250, 500) * 1000);
  }
  return $urls;
}

$urls = getUrlsFromSitemapIndex(1, 50, 800);
$count = 0;
foreach ($urls as $url) {
  $html = curl_get($url);
  $car = parseListingCardHtml($html);
  if (!$car) continue;
  $car['source_url'] = $url;
  saveCarData($car);
  $count++;
  if ($count % 50 === 0) fwrite(STDERR, "Saved: $count\n");
  usleep(random_int(250,500)*1000);
}
