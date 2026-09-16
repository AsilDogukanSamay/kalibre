<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Oturum katmani.
 *
 * Oturum yalnizca yonetim panelinde acilir; ziyaretci tarafinda tek bir
 * cerez bile olusmaz. Gizlilik metnindeki "bu site cerez kullanmaz"
 * ifadesinin dogru kalmasi buna bagli.
 */
final class Session
{
    private const NAME = 'kalibre_panel';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(self::NAME);
        session_set_cookie_params([
            'lifetime' => 0,               // tarayici kapaninca silinir
            'path'     => '/yonetim',      // cerez yalnizca panel yolunda gonderilir
            'httponly' => true,            // JavaScript okuyamaz
            'samesite' => 'Strict',        // siteler arasi istekte gonderilmez
            'secure'   => Security::isHttps(),
        ]);
        session_start();
    }

    /** Oturum sabitleme (session fixation) saldirisina karsi giristen sonra cagrilir. */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => Security::isHttps(),
            ]);
        }

        session_destroy();
    }

    /**
     * CSRF belirteci. Oturum basina bir kez uretilir, her form ile gonderilir.
     * Panel formlari POST ile calisir; belirtec dogrulanmadan hicbir durum degismez.
     */
    public static function csrfToken(): string
    {
        $token = self::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            self::set('_csrf', $token);
        }

        return $token;
    }

    public static function verifyCsrf(string $token): bool
    {
        $expected = self::get('_csrf');
        return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
    }

    /** Tek seferlik bildirim mesaji (Post/Redirect/Get sonrasi gosterilir). */
    public static function flash(?string $message = null): ?string
    {
        if ($message !== null) {
            self::set('_flash', $message);
            return null;
        }

        $value = self::get('_flash');
        self::forget('_flash');

        return is_string($value) ? $value : null;
    }
}
