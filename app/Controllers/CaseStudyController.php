<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Support\Brand;
use App\Support\CaseStudy;
use App\Support\SiteContent;

/**
 * GET /case
 * Urunun kendisini degil, urun kararlarini sergileyen sayfa.
 * Tasarim sistemi token'lari canli olarak ayni CSS'ten okunur,
 * yani burada gordugunuz her deger sitenin gercekten kullandigi degerdir.
 */
final class CaseStudyController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('case', CaseStudy::all() + [
            'brand'      => SiteContent::all()['brand'],
            'brandTheme' => Brand::current(),
            'brands'     => Brand::all(),
            // Kendi basligi olmadan ana sayfanin varsayilanina dusuyordu:
            // iki sayfa ayni sekme adini ve ayni meta aciklamasini tasiyordu.
            'pageTitle'       => 'Vaka çalışması',
            'pageDescription' => 'Boya düzeltme atölyesi için hazırlanan landing '
                . 'page ve iletişim modülünün vaka çalışması: problem tanımı, '
                . 'tasarım kararları, ölçülen sonuçlar ve her kararın bedeli.',
        ]);
    }
}
