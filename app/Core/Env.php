<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env okuyucu.
 * Hassas yapilandirma kod tabaninda degil, versiyonlanmayan .env dosyasinda durur.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $data = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        if (!is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Satir sonu yorumu: DEGER=x   # aciklama
            // Yalnizca tirnaksiz degerlerde kirpilir; tirnak icindeki # korunur.
            if ($value !== '' && $value[0] !== '"' && $value[0] !== "'") {
                $yorum = strpos($value, '#');
                if ($yorum !== false) {
                    $value = rtrim(substr($value, 0, $yorum));
                }
            }

            // "deger" veya 'deger' sarmalayicilarini soy
            if (strlen($value) > 1) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }
            self::$data[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::$data[$key] ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $raw = self::get($key);
        if ($raw === null) {
            return $default;
        }
        return in_array(strtolower($raw), ['1', 'true', 'on', 'yes'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $raw = self::get($key);
        return $raw === null || !is_numeric($raw) ? $default : (int) $raw;
    }
}
