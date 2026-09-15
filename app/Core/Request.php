<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP istegini sarar. Superglobal'lara dogrudan erisim tek noktada toplanir.
 */
final class Request
{
    /** @param array<string,mixed> $payload */
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $payload
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path   = '/' . trim((string) $path, '/');

        $payload = $_GET;
        if ($method !== 'GET') {
            $raw  = (string) file_get_contents('php://input');
            $type = $_SERVER['CONTENT_TYPE'] ?? '';

            if (str_contains($type, 'application/json') && $raw !== '') {
                $decoded = json_decode($raw, true);
                $payload = is_array($decoded) ? $decoded : [];
            } else {
                $payload = $_POST;
            }
        }

        return new self($method, $path, $payload);
    }

    public function input(string $key, string $default = ''): string
    {
        $value = $this->payload[$key] ?? $default;
        return is_scalar($value) ? (string) $value : $default;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->payload;
    }

    public function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}
