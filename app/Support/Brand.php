<?php
declare(strict_types=1);

namespace App\Support;

use App\Core\Env;

/**
 * Marka katmani.
 *
 * Tasarim sistemi marka-bagimsiz kurulmustur: renkler CSS degiskenlerine,
 * Tailwind token'lari da o degiskenlere baglidir. Marka degistirmek icin
 * tek bir .env satiri yeterlidir, hicbir sablon veya sinif degismez.
 */
final class Brand
{
    private const BRANDS = [
        'bosch' => [
            'key'        => 'bosch',
            'name'       => 'Bosch Car Service',
            'wordmark'   => 'BOSCH',
            'tagline'    => 'Car Service',
            'logo'       => 'partials/logo-bosch',
            'favicon'    => 'img/favicon-bosch.svg',
            'legal'      => 'Robert Bosch GmbH',
            'typeface'   => 'Bosch Sans (lisansli) yerine Archivo',
            'palette'    => ['#EA0016', '#00509D', '#008ECF'],
            'disclaimer' => true,
        ],
        'kalibre' => [
            'key'        => 'kalibre',
            'name'       => 'Kalibre',
            'wordmark'   => 'KALIBRE',
            'tagline'    => 'Detailing Studio',
            'logo'       => 'partials/logo-kalibre',
            'favicon'    => 'img/favicon-kalibre.svg',
            'legal'      => null,
            'typeface'   => 'Archivo',
            'palette'    => ['#4B7BFF', '#3D6BF0', '#7A9CFF'],
            'disclaimer' => false,
        ],
    ];

    /** @return array<string,mixed> */
    public static function current(): array
    {
        $key = strtolower((string) Env::get('APP_BRAND', 'bosch'));
        return self::BRANDS[$key] ?? self::BRANDS['bosch'];
    }

    public static function key(): string
    {
        return (string) self::current()['key'];
    }

    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return self::BRANDS;
    }
}
