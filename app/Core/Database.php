<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Tek PDO baglantisi uretir ve paylasir.
 * Baglanti bilgileri yalnizca .env uzerinden gelir, kodda sabit deger yoktur.
 */
final class Database
{
    private static ?PDO $pdo = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            Env::get('DB_CONNECTION', 'mysql'),
            Env::get('DB_HOST', '127.0.0.1'),
            Env::int('DB_PORT', 3306),
            (string) Env::get('DB_DATABASE', ''),
            Env::get('DB_CHARSET', 'utf8mb4')
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                (string) Env::get('DB_USERNAME', ''),
                (string) Env::get('DB_PASSWORD', ''),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Gercek prepared statement kullanilir, emulasyon kapali.
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            // Baglanti detayi son kullaniciya sizdirilmaz.
            throw new RuntimeException('Veritabanı bağlantısı kurulamadı.', 0, $e);
        }

        return self::$pdo;
    }

    /** Testlerde sahte baglanti enjekte etmek icin. */
    public static function swap(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
