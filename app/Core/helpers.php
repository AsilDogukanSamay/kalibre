<?php
declare(strict_types=1);

/**
 * Sablonlar icin kisa yardimcilar.
 * Ciktiya giden her dinamik deger e() uzerinden gecer (XSS).
 */

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('partial')) {
    /** @param array<string,mixed> $data */
    function partial(string $template, array $data = []): string
    {
        return App\Core\View::partial($template, $data);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_exists')) {
    /** Varlik dosyasi public/assets altinda gercekten var mi? */
    function asset_exists(string $path): bool
    {
        return is_file(dirname(__DIR__, 2) . '/public/assets/' . ltrim($path, '/'));
    }
}
