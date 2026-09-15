<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Sade sablon motoru. Sablonlar app/Views altinda yasar,
 * her sablon $layout icindeki $content olarak basilir.
 */
final class View
{
    /** @param array<string,mixed> $data */
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::capture($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture($layout, $data + ['content' => $content]);
    }

    /** Parcalari sablon icinden cagirmak icin. */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    /**
     * XSS kacisi. Ciktiya giden her dinamik deger buradan gecer.
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** @param array<string,mixed> $data */
    private static function capture(string $template, array $data): string
    {
        $path = dirname(__DIR__) . '/Views/' . $template . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Sablon bulunamadi: %s', $template));
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
