<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Guvenlik basliklari ve istek basina uretilen script nonce'u.
 *
 * Basliklar tek noktada uretilir; boylece hem HTML yanitlari hem de
 * testler ayni kaynagi okur.
 */
final class Security
{
    private static ?string $nonce = null;

    /** Istek basina bir kez uretilir, ayni istekte hep ayni degeri doner. */
    public static function nonce(): string
    {
        return self::$nonce ??= base64_encode(random_bytes(16));
    }

    public static function isHttps(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        // Ters vekil arkasinda (nginx, Cloudflare) sema bu baslikta gelir.
        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    /** @return array<string,string> */
    public static function htmlHeaders(): array
    {
        $headers = [
            'X-Content-Type-Options'  => 'nosniff',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'X-Frame-Options'         => 'DENY',
            'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Content-Security-Policy' => self::csp(),
        ];

        // HSTS yalnizca HTTPS uzerinden anlamlidir; duz HTTP'de gonderilmesi
        // tarayici tarafindan zaten yok sayilir, gondermiyoruz.
        if (self::isHttps()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $headers;
    }

    /**
     * script-src nonce ile kilitlidir: sayfadaki tek satir ici blok olan
     * JSON-LD nonce tasir, geri kalan tum betikler harici dosyadir.
     * Boylece sayfaya enjekte edilen bir <script> calismaz.
     *
     * style-src 'unsafe-inline' bilincli ve dar bir tavizdir. HTML'de style
     * attribute'u yoktur; ancak surgu konumu ve scroll ilerlemesi, sayfaya bir
     * kez eklenen bos bir <style> elemanina insertRule ile yazilir ve tarayici
     * bunu satir ici stil sayar. Alternatifi constructible stylesheet'tir,
     * eski Safari surumlerinde desteklenmez. Taviz stile ait; betik tarafi
     * (XSS'in gercek yuzeyi) nonce ile kapalidir.
     */
    public static function csp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "img-src 'self' data:",
            "media-src 'self'",
            "font-src 'self' https://fonts.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "script-src 'self' 'nonce-" . self::nonce() . "'",
            "connect-src 'self'",
        ]);
    }
}
