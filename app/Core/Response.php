<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tek tip cikti katmani. Tum JSON sozlesmesi burada uretilir (DRY).
 */
final class Response
{
    /** @param array<string,mixed> $data */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** @param array<string,string> $errors */
    public static function fail(string $message, array $errors = [], int $status = 422): void
    {
        self::json(['ok' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    /** @param array<string,mixed> $extra */
    public static function ok(string $message, array $extra = []): void
    {
        self::json(['ok' => true, 'message' => $message] + $extra);
    }

    public static function html(string $body, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        echo $body;
    }
}
