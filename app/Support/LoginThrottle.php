<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Panel girisinde kaba kuvvet freni.
 *
 * Sayac oturumda degil dosyada tutulur: oturumda tutulsaydi saldirgan
 * cerezi silerek sayaci sifirlardi. Anahtar IP adresidir.
 *
 * Tek dosya + flock, tek sunuculu kurulum icin yeterlidir. Birden fazla
 * uygulama sunucusu olsaydi bu sayacin paylasilan bir depoda (Redis veya
 * veritabani) tutulmasi gerekirdi.
 */
final class LoginThrottle
{
    private const LIMIT   = 5;   // izin verilen basarisiz deneme
    private const WINDOW  = 900; // saniye (15 dakika)

    public function __construct(private ?string $path = null)
    {
        $this->path ??= dirname(__DIR__, 2) . '/storage/login-denemeleri.json';
    }

    /** Bu IP su an kilitli mi? */
    public function isLocked(string $ip): bool
    {
        return $this->remaining($ip) <= 0;
    }

    /** Kilide kadar kalan deneme hakki. */
    public function remaining(string $ip): int
    {
        $attempts = $this->attemptsFor($ip, $this->read());
        return max(0, self::LIMIT - count($attempts));
    }

    /** Kilidin acilmasina kalan saniye. */
    public function retryAfter(string $ip): int
    {
        $attempts = $this->attemptsFor($ip, $this->read());
        if ($attempts === []) {
            return 0;
        }

        return max(0, (int) min($attempts) + self::WINDOW - time());
    }

    public function recordFailure(string $ip): void
    {
        $data = $this->read();
        $data[$ip] = array_merge($this->attemptsFor($ip, $data), [time()]);
        $this->write($this->prune($data));
    }

    public function clear(string $ip): void
    {
        $data = $this->read();
        unset($data[$ip]);
        $this->write($data);
    }

    /**
     * @param array<string,array<int,int>> $data
     * @return array<int,int>
     */
    private function attemptsFor(string $ip, array $data): array
    {
        $cutoff = time() - self::WINDOW;
        return array_values(array_filter(
            $data[$ip] ?? [],
            static fn (int $at): bool => $at > $cutoff
        ));
    }

    /**
     * Suresi dolmus kayitlari atar; dosya sinirsiz buyumez.
     *
     * @param array<string,array<int,int>> $data
     * @return array<string,array<int,int>>
     */
    private function prune(array $data): array
    {
        $temiz = [];
        foreach (array_keys($data) as $ip) {
            $attempts = $this->attemptsFor((string) $ip, $data);
            if ($attempts !== []) {
                $temiz[(string) $ip] = $attempts;
            }
        }

        return $temiz;
    }

    /** @return array<string,array<int,int>> */
    private function read(): array
    {
        if (!is_readable($this->path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($this->path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string,array<int,int>> $data */
    private function write(array $data): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        file_put_contents(
            $this->path,
            json_encode($data, JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}
