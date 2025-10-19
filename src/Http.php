<?php
require_once __DIR__.'/Config.php';

function curl_get(string $url, int $timeout=15): string {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_USERAGENT      => SCRAPER_UA,
    CURLOPT_HTTPHEADER     => ['Accept: text/html,application/xhtml+xml,application/ld+json'],
  ]);
  $html = curl_exec($ch);
  if ($html === false) throw new RuntimeException(curl_error($ch));
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code >= 400) throw new RuntimeException("HTTP $code for $url");
  return $html ?: '';
}
