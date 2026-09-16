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

        foreach (Security::htmlHeaders() as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $body;
    }

    /** Duz metin cikti (robots.txt) ve XML cikti (sitemap.xml) icin. */
    public static function text(string $body, string $contentType = 'text/plain; charset=utf-8'): void
    {
        http_response_code(200);
        header('Content-Type: ' . $contentType);
        header('X-Content-Type-Options: nosniff');
        echo $body;
    }

    /** Post/Redirect/Get: form gonderiminden sonra tazeleme kaydi tekrarlamaz. */
    public static function redirect(string $path, int $status = 303): void
    {
        http_response_code($status);
        header('Location: ' . $path);
    }
}
