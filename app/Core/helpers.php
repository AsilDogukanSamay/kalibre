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
    /**
     * Varlik adresi + surum damgasi.
     *
     * Statik dosyalar uzun sureli onbelleklenir. Adres sabit kalsaydi,
     * yayina alinan yeni CSS/JS geri donen ziyaretciye gunlerce ulasmazdi;
     * gelistirirken de tarayici eski kopyayi sunmaya devam ederdi.
     * Dosyanin degisme zamani adrese eklenince adres de degisir, yani
     * "surum atlamayi unutma" diye bir adim kalmaz.
     */
    function asset(string $path): string
    {
        $rel = ltrim($path, '/');
        $url = '/assets/' . $rel;
        $tam = dirname(__DIR__, 2) . '/public/assets/' . $rel;

        $zaman = is_file($tam) ? filemtime($tam) : false;

        return $zaman === false ? $url : $url . '?v=' . dechex($zaman);
    }
}

if (!function_exists('asset_exists')) {
    /** Varlik dosyasi public/assets altinda gercekten var mi? */
    function asset_exists(string $path): bool
    {
        return is_file(dirname(__DIR__, 2) . '/public/assets/' . ltrim($path, '/'));
    }
}
