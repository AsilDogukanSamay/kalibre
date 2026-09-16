<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Support\LegalContent;

/**
 * robots.txt ve sitemap.xml.
 *
 * Statik dosya olarak birakilsalardi alan adi iki yerde yazili olurdu.
 * Rota olarak uretilince mutlak adresler .env'deki APP_URL'den gelir;
 * yayina alirken tek satir degistirmek yeterlidir.
 */
final class SeoController extends Controller
{
    /** Sitemap'e girecek genel sayfalar: yol => degisim sikligi, oncelik. */
    private const PUBLIC_PATHS = [
        '/'     => ['weekly', '1.0'],
        '/case' => ['monthly', '0.6'],
    ];

    public function robots(Request $request): void
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Yonetim paneli aranabilir olmamali',
            'Disallow: /yonetim',
            '',
            'Sitemap: ' . $this->siteUrl() . '/sitemap.xml',
            '',
        ];

        Response::text(implode("\n", $lines));
    }

    public function sitemap(Request $request): void
    {
        $siteUrl = $this->siteUrl();
        $today   = date('Y-m-d');

        $paths = self::PUBLIC_PATHS;
        foreach (LegalContent::pages() as $page) {
            $paths[$page['path']] = ['yearly', '0.3'];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($paths as $path => [$frequency, $priority]) {
            $loc = htmlspecialchars($siteUrl . $path, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n"
                 .  "    <loc>{$loc}</loc>\n"
                 .  "    <lastmod>{$today}</lastmod>\n"
                 .  "    <changefreq>{$frequency}</changefreq>\n"
                 .  "    <priority>{$priority}</priority>\n"
                 .  "  </url>\n";
        }

        $xml .= '</urlset>' . "\n";

        Response::text($xml, 'application/xml; charset=utf-8');
    }

    private function siteUrl(): string
    {
        return rtrim((string) Env::get('APP_URL', 'http://localhost:5174'), '/');
    }
}
