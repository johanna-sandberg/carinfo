<?php

declare(strict_types=1);

namespace CarInfo\Http;

final class CurlClient
{
  public function fetchHtml(string $url, int $requestTimeoutSeconds = 20, int $maxRetries = 3): string
  {
    $backoffMs = 400;

    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $requestTimeoutSeconds,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT      => \SCRAPER_UA ?: 'Mozilla/5.0',
        CURLOPT_ENCODING       => '',             // gzip/br om servern stödjer
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        CURLOPT_HTTPHEADER     => [
          'Accept: text/html,application/xhtml+xml,application/ld+json;q=0.9,*/*;q=0.8',
          'Accept-Language: sv-SE,sv;q=0.9,en-US;q=0.8,en;q=0.7',
          'Cache-Control: no-cache',
          'Pragma: no-cache',
        ],
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2TLS,
        CURLOPT_TCP_KEEPALIVE  => 1,
        CURLOPT_TCP_KEEPIDLE   => 30,
        CURLOPT_TCP_KEEPINTVL  => 15,
        CURLOPT_TCP_NODELAY    => 1,
      ]);

      $body = curl_exec($ch);
      $err  = curl_error($ch);
      $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($body !== false && $body !== '' && $code < 400) {
        return $body;
      }

      if ($attempt === $maxRetries) {
        $msg = $body === false ? $err : "HTTP $code for $url";
        throw new \RuntimeException($msg);
      }

      usleep(($backoffMs + random_int(0, 300)) * 1000);
      $backoffMs = min(2500, (int)($backoffMs * 1.6));
    }

    return '';
  }
}
