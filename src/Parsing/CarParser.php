<?php

declare(strict_types=1);

namespace CarInfo\Parsing;

use DOMDocument;
use DOMXPath;

final class CarParser
{
  public function parseDetailPageHtml(string $html): array
  {
    if ($this->htmlLooksLikeSearchPage($html)) {
      return [];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xp = new DOMXPath($dom);
    $car = [];

    $car['title'] = trim($this->textFirst($xp, "//h1")) ?: trim($this->textFirst($xp, "//h2"));

    $kv = $this->extractKeyValuePairs($xp);

    $car['reg_plate']  = $this->pickCaseInsensitive($kv, ['Regnummer', 'Regnr', 'Reg nr', 'Reg-nr']);
    $car['brand']      = $this->pickCaseInsensitive($kv, ['Märke', 'Marke', 'Bilmärke', 'Bilmarke']) ?: $this->valueByExactLabel($xp, 'Märke');
    $car['model']      = $this->pickCaseInsensitive($kv, ['Modell', 'Model']) ?: $this->valueByExactLabel($xp, 'Modell');
    $car['body']       = $this->pickCaseInsensitive($kv, ['Fordonstyp', 'Kaross', 'Karosstyp']) ?: $this->valueByExactLabel($xp, 'Fordonstyp');
    $car['gearbox']    = $this->pickCaseInsensitive($kv, ['Växellåda', 'Vaxellada', 'Automatisk', 'Manuell']) ?: $this->valueByExactLabel($xp, 'Växellåda');
    $car['fuel']       = $this->pickCaseInsensitive($kv, ['Drivmedel', 'Bränsle', 'Bransle', 'Bensin', 'Diesel', 'El', 'Hybrid']) ?: $this->valueByExactLabel($xp, 'Drivmedel');
    $car['location']   = $this->pickCaseInsensitive($kv, ['Ort', 'Stad', 'Location', 'Ort/Adress']) ?: $this->valueByExactLabel($xp, 'Ort');
    $car['color']      = $this->pickCaseInsensitive($kv, ['Färg', 'Farg', 'Color']) ?: $this->valueByExactLabel($xp, 'Färg');

    $modelYear = $this->pickCaseInsensitive($kv, ['Årsmodell', 'Arsmodell', 'År', 'Ar', 'Modellår', 'Model year']) ?: $this->valueByExactLabel($xp, 'Årsmodell');
    $car['model_year'] = $this->toInt($modelYear);

    $hpText = $this->pickCaseInsensitive($kv, ['Hästkrafter', 'Hastkrafter', 'HK', 'Motoreffekt'])
      ?: $this->valueByExactLabel($xp, 'Hästkrafter')
      ?: $this->valueByExactLabel($xp, 'HK')
      ?: $this->valueByExactLabel($xp, 'Motoreffekt');
    $car['horsepower'] = $this->toInt($hpText);

    $mileage = $this->pickCaseInsensitive($kv, ['Mil', 'Miltal', 'Mileage', 'Odometer']) ?: $this->valueByExactLabel($xp, 'Mil');
    if ($mileage !== null && $mileage !== '') {
      $car['mileage_km'] = $this->mileageToKm($mileage);
    }

    $firstReg = $this->pickCaseInsensitive($kv, ['1:a regdatum', 'Första regdatum', 'Forsta regdatum', 'Registreringsdatum']) ?: $this->valueByExactLabel($xp, '1:a regdatum');
    if ($firstReg) {
      $car['listed_at'] = $this->normalizeDate($firstReg);
    }

    $manufactured = $this->pickCaseInsensitive($kv, ['Tillv.mån', 'Tillv man', 'Tillv.månad', 'Tillv månad', 'Tillv', 'Tillv månad/år']) ?: $this->valueByExactLabel($xp, 'Tillv.mån');
    if ($manufactured) {
      $car['manufactured_month'] = $this->normalizeYearMonth($manufactured);
    }

    $car['price_sek'] = $this->priceFromDom($xp) ?? $this->firstPriceSek($html);

    $dealer = trim($this->textFirst($xp, "//strong[ancestor::span[contains(., 'SÄLJES AV')]]"))
      ?: trim($this->textFirst($xp, "//h4[contains(@class,'vehicle-user-name')]"));
    if ($dealer !== '') {
      $car['dealer_name'] = $dealer;
    }

    $desc = trim($this->textFirst($xp, "//*[contains(@class,'viewDescription')]"));
    if ($desc !== '') {
      $car['description'] = $desc;
    }

    if (isset($car['reg_plate'])) {
      $car['reg_plate'] = strtoupper(trim($car['reg_plate']));
    }

    return array_filter($car, static fn($v) => $v !== null && $v !== '');
  }

  private function valueByExactLabel(DOMXPath $xp, string $label): ?string
  {
    $node = $xp->query("//li[h5[normalize-space()='{$label}']]/p | //li[.//h5[normalize-space()='{$label}']]//p | //dt[normalize-space()='{$label}']/following-sibling::dd[1]")->item(0);
    if (!$node) {
      return null;
    }
    $text = trim($node->textContent);
    return $text === '' ? null : $text;
  }

  private function htmlLooksLikeSearchPage(string $html): bool
  {
    if (preg_match('~<h1[^>]*>\s*Sökresultat\s*</h1>~iu', $html)) {
      return true;
    }
    if (preg_match('~class=["\']Card\b~i', $html) && preg_match('~/sok\?~i', $html)) {
      return true;
    }
    return false;
  }

  private function textFirst(DOMXPath $xp, string $query): string
  {
    $n = $xp->query($query)->item(0);
    return $n ? trim($n->textContent) : '';
  }

  private function extractKeyValuePairs(DOMXPath $xp): array
  {
    $pairs = [];

    foreach ($xp->query("//ul[contains(@class,'List')]//li") as $li) {
      $label = trim($xp->query(".//h5", $li)->item(0)?->textContent ?? '');
      $valueNode = $xp->query(".//p", $li)->item(0);
      $value = $valueNode ? trim($valueNode->textContent) : '';
      if ($label !== '' && $value !== '') {
        $pairs[$label] = $value;
      }
    }

    foreach ($xp->query('//dl') as $dl) {
      $dts = [];
      foreach ($dl->childNodes as $child) {
        $tag = strtolower($child->nodeName);
        if ($tag === 'dt') {
          $dts[] = $child;
        }
        if ($tag === 'dd' && $dts) {
          $label = trim($dts[count($dts) - 1]->textContent ?? '');
          $value = trim($child->textContent ?? '');
          if ($label !== '' && $value !== '') {
            $pairs[$label] = $value;
          }
        }
      }
    }

    foreach ($xp->query("//*[contains(@class,'spec') or contains(@class,'details') or contains(@class,'fact') or contains(@class,'attributes')]//li") as $li) {
      $text = trim($li->textContent ?? '');
      if ($text === '') {
        continue;
      }
      if (strpos($text, ':') !== false) {
        [$k, $v] = array_map('trim', explode(':', $text, 2));
        if ($k !== '' && $v !== '') {
          $pairs[$k] = $v;
        }
      } else {
        $label = trim($xp->query(".//*[contains(@class,'label') or contains(@class,'key')]", $li)->item(0)?->textContent ?? '');
        $value = trim($xp->query(".//*[contains(@class,'value') or contains(@class,'val')]", $li)->item(0)?->textContent ?? '');
        if ($label !== '' && $value !== '') {
          $pairs[$label] = $value;
        }
      }
    }

    return $pairs;
  }

  private function pickCaseInsensitive(array $map, array $candidateKeys): ?string
  {
    $lowered = [];
    foreach ($map as $k => $v) {
      $lowered[mb_strtolower($k, 'UTF-8')] = $v;
    }
    foreach ($candidateKeys as $key) {
      $lk = mb_strtolower($key, 'UTF-8');
      if (array_key_exists($lk, $lowered)) {
        return trim($lowered[$lk]);
      }
    }
    return null;
  }

  private function priceFromDom(DOMXPath $xp): ?int
  {
    $n = $xp->query("//*[contains(@class,'viewPrice') and contains(., 'kr')]")->item(0);
    if (!$n) {
      return null;
    }
    $text = trim($n->textContent);
    $digits = preg_replace('/\D+/', '', $text);
    return $digits === '' ? null : (int)$digits;
  }

  private function firstPriceSek(string $html): ?int
  {
    if (preg_match('/(\d[\d\s]{2,})\s*kr/i', $html, $m)) {
      $digits = preg_replace('/\D+/', '', $m[1]);
      if ($digits !== '') {
        return (int)$digits;
      }
    }
    return null;
  }

  private function toInt(?string $value): ?int
  {
    if ($value === null) {
      return null;
    }
    $digits = preg_replace('/\D+/', '', $value);
    return $digits === '' ? null : (int)$digits;
  }

  private function mileageToKm(string $value): ?int
  {
    $digits = preg_replace('/\D+/', '', $value);
    if ($digits === '') {
      return null;
    }
    if (preg_match('/\bkm\b/i', $value)) {
      return (int)$digits;
    }
    return (int)$digits * 10;
  }

  private function normalizeDate(string $input): ?string
  {
    $input = trim($input);
    if (preg_match('/\d{4}-\d{2}-\d{2}/', $input, $m)) {
      return $m[0];
    }
    if (preg_match('/(\d{1,2})[\/\.\- ](\d{1,2})[\/\.\- ](\d{2,4})/', $input, $m)) {
      $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
      $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
      $year = (int)$m[3];
      if ($year < 100) {
        $year += 2000;
      }
      return sprintf('%04d-%02d-%02d', $year, (int)$month, (int)$day);
    }
    return null;
  }

  private function normalizeYearMonth(string $input): ?string
  {
    $input = trim($input);
    if (preg_match('/(\d{4})[^\d]?(\d{1,2})/', $input, $m)) {
      return sprintf('%04d-%02d', (int)$m[1], (int)$m[2]);
    }
    return null;
  }
}
