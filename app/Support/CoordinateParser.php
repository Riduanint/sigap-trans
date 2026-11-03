<?php

namespace App\Support;

class CoordinateParser
{
    /**
     * Parse raw coordinate string into array of latitude and longitude (float) and DMS string.
     *
     * Supports formats:
     * - DMS with symbols: 2°25'59.9"S 115°40'49.2"E or 2°25'59.9"S, 115°40'49.2"E
     * - DMS spaced: 2 25 59.9 S 115 40 49.2 E
     * - Decimal degrees: -2.433306, 115.680333 or -2.433306 115.680333
     * - Decimal with cardinal: 2.433306 S, 115.680333 E
     *
     * @param string|null $raw
     * @return array{latitude: float, longitude: float, dms: string}|null
     */
    public static function parse(?string $raw): ?array
    {
        if (empty($raw)) {
            return null;
        }

        $input = trim($raw);
        $input = trim($input, "()[]{} \t\n\r\0\x0B");

        // 1. Try Parsing Standard Decimal Format: e.g. "-2.433306, 115.680333" or "-2.433306 115.680333"
        // Also matches "2.433306S 115.680333E"
        $decWithCardinalPattern = '/^([+-]?\d+(?:\.\d+)?)\s*([NSns]?)[,\s;]+([+-]?\d+(?:\.\d+)?)\s*([EWew]?)$/u';
        if (preg_match($decWithCardinalPattern, $input, $m)) {
            $latVal = (float) $m[1];
            $latCard = strtoupper($m[2] ?? '');
            $lngVal = (float) $m[3];
            $lngCard = strtoupper($m[4] ?? '');

            if ($latCard === 'S' && $latVal > 0) $latVal = -$latVal;
            if ($latCard === 'N' && $latVal < 0) $latVal = abs($latVal);
            if ($lngCard === 'W' && $lngVal > 0) $lngVal = -$lngVal;
            if ($lngCard === 'E' && $lngVal < 0) $lngVal = abs($lngVal);

            // Basic sanity check for valid world coordinates
            if ($latVal >= -90 && $latVal <= 90 && $lngVal >= -180 && $lngVal <= 180) {
                return [
                    'latitude' => round($latVal, 6),
                    'longitude' => round($lngVal, 6),
                    'dms' => self::toDms($latVal, $lngVal),
                ];
            }
        }

        // 2. Try Parsing DMS Format
        // Replace non-breaking spaces and normalize degree/minute/second quotes
        $normalized = str_replace(
            ['’', '‘', '`', '′', '”', '“', '″', 'º'],
            ["'", "'", "'", "'", '"', '"', '"', '°'],
            $input
        );

        // Pattern matching DMS part: (deg)[° ]?(min)[' ]?(sec)?[" ]?([NSEW])?
        // Example: 2°25'59.9"S 115°40'49.2"E
        $dmsPartRegex = '(\d+)\s*[°dD\s]\s*(\d+)\s*[\'mM\s]\s*(\d+(?:\.\d+)?)\s*["sS\s]*([NSnsEWew])?';

        // Match two DMS parts separated by comma, space, or semicolon
        $fullDmsRegex = '/^' . $dmsPartRegex . '[,;\s]+' . $dmsPartRegex . '$/u';

        if (preg_match($fullDmsRegex, $normalized, $matches)) {
            $latDeg = (float) $matches[1];
            $latMin = (float) $matches[2];
            $latSec = (float) $matches[3];
            $latDir = strtoupper($matches[4] ?? '');

            $lngDeg = (float) $matches[5];
            $lngMin = (float) $matches[6];
            $lngSec = (float) $matches[7];
            $lngDir = strtoupper($matches[8] ?? '');

            $lat = $latDeg + ($latMin / 60) + ($latSec / 3600);
            $lng = $lngDeg + ($lngMin / 60) + ($lngSec / 3600);

            // Default Indonesia: S (South) and E (East) if directions are not explicitly North/West
            if ($latDir === 'S' || empty($latDir)) {
                $lat = -$lat;
            } elseif ($latDir === 'N') {
                $lat = abs($lat);
            }

            if ($lngDir === 'W') {
                $lng = -$lng;
            } else {
                $lng = abs($lng);
            }

            // Verify if values swapped (e.g. user entered Longitude first then Latitude)
            if (abs($lat) > 90 && abs($lng) <= 90) {
                $temp = $lat;
                $lat = $lng;
                $lng = $temp;
            }

            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                return [
                    'latitude' => round($lat, 6),
                    'longitude' => round($lng, 6),
                    'dms' => self::toDms($lat, $lng),
                ];
            }
        }

        // 3. Fallback Pattern: Regex finding any pair of coordinate numbers (including DMS components)
        // Extract all numbers and letters
        if (preg_match('/(\d+)°?\s*(\d+)\'?\s*(\d+(?:\.\d+)?)"?\s*([NSns])?[,\s]+(\d+)°?\s*(\d+)\'?\s*(\d+(?:\.\d+)?)"?\s*([EWew])?/u', $normalized, $m)) {
            $lat = (float)$m[1] + ((float)$m[2] / 60) + ((float)$m[3] / 3600);
            $latDir = strtoupper($m[4] ?? 'S');
            if ($latDir === 'S') $lat = -$lat;

            $lng = (float)$m[5] + ((float)$m[6] / 60) + ((float)$m[7] / 3600);
            $lngDir = strtoupper($m[8] ?? 'E');
            if ($lngDir === 'W') $lng = -$lng;

            return [
                'latitude' => round($lat, 6),
                'longitude' => round($lng, 6),
                'dms' => self::toDms($lat, $lng),
            ];
        }

        return null;
    }

    /**
     * Convert decimal coordinates to standard DMS string format.
     * Example: -2.433306, 115.680333 -> 2°25'59.9"S 115°40'49.2"E
     */
    public static function toDms(float $lat, float $lng): string
    {
        $latDir = $lat < 0 ? 'S' : 'N';
        $latAbs = abs($lat);
        $latDeg = floor($latAbs);
        $latMinRemainder = ($latAbs - $latDeg) * 60;
        $latMin = floor($latMinRemainder);
        $latSec = round(($latMinRemainder - $latMin) * 60, 1);
        if ($latSec >= 60) {
            $latMin += 1;
            $latSec = 0.0;
        }
        if ($latMin >= 60) {
            $latDeg += 1;
            $latMin = 0;
        }

        $lngDir = $lng < 0 ? 'W' : 'E';
        $lngAbs = abs($lng);
        $lngDeg = floor($lngAbs);
        $lngMinRemainder = ($lngAbs - $lngDeg) * 60;
        $lngMin = floor($lngMinRemainder);
        $lngSec = round(($lngMinRemainder - $lngMin) * 60, 1);
        if ($lngSec >= 60) {
            $lngMin += 1;
            $lngSec = 0.0;
        }
        if ($lngMin >= 60) {
            $lngDeg += 1;
            $lngMin = 0;
        }

        $secLatFormatted = number_format($latSec, 1, '.', '');
        $secLngFormatted = number_format($lngSec, 1, '.', '');

        return "{$latDeg}°{$latMin}'{$secLatFormatted}\"{$latDir} {$lngDeg}°{$lngMin}'{$secLngFormatted}\"{$lngDir}";
    }

    /**
     * Check if latitude and longitude are within South Kalimantan bounding box.
     */
    public static function isWithinKalsel(float $lat, float $lng): bool
    {
        return ($lat >= -5.5 && $lat <= -1.0) && ($lng >= 114.0 && $lng <= 117.5);
    }
}
