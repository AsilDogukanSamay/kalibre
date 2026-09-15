<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PSR-4 uyumlu sade otomatik yukleyici.
 * Composer bagimliligi olmadan App ad alanini app/ dizinine esler.
 */
final class Autoloader
{
    public static function register(string $baseDir, string $prefix = 'App\\'): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir, $prefix): void {
            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
