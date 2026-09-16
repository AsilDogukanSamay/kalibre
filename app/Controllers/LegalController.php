<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Support\Brand;
use App\Support\LegalContent;
use App\Support\SiteContent;

/**
 * GET /kvkk  ve  GET /gizlilik
 *
 * Iki rota tek denetleyiciden beslenir; hangi metnin basilacagi
 * LegalContent::pages() kaydindan yol eslesmesiyle bulunur.
 */
final class LegalController extends Controller
{
    public function show(Request $request): void
    {
        $pages = LegalContent::pages();
        $brand = SiteContent::all()['brand'];

        // Sayfanin ustunde gorunen marka ile metindeki veri sorumlusu adi
        // ayni olmali; ad etkin temadan okunur.
        $brand['name'] = (string) Brand::current()['name'];

        foreach ($pages as $kayit) {
            if ($kayit['path'] !== $request->path) {
                continue;
            }

            /** @var callable(array<string,mixed>):array<string,mixed> $uretici */
            $uretici = [LegalContent::class, $kayit['method']];
            $page    = $uretici($brand) + ['path' => $kayit['path']];

            $this->view('legal', [
                'page'       => $page,
                'legalPages' => $pages,
                'brand'      => $brand,
                'pageTitle'  => $kayit['title'],
            ]);
            return;
        }

        // Rota tablosu ile LegalContent::pages() birbirinden ayrilmis demektir.
        $this->notFound();
    }
}
