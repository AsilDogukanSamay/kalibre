<?php
/**
 * Ana sayfa bolumleri.
 *
 * Bolumler tek tek degil, TONAL BOLUM (chapter) halinde diziliyor. Her chapter
 * kendi yuzey tonunda durur ve icindeki bolumler o tonu paylasir:
 *
 *   1. Acilis      hero                              (video)
 *   2. Kanit       olcu seridi, sürgü, scroll pasosu (varsayilan ton)
 *   3. Teklif      hizmetler, surec, fiyat           (yukseltilmis ton)
 *   4. Manifesto   tek cumle                          (sessiz ton)
 *   5. Guven       referanslar, sss                  (varsayilan ton)
 *   6. Eylem       iletisim                          (yukseltilmis ton)
 *
 * Neden grup: her bolume sirayla ton vermek serit etkisi yapiyordu. Ton
 * degisimi artik "yeni bolum basladi" demek; ritmi tasiyan sey bu.
 *
 * Icerik yine SiteContent sinifindan tek kaynak olarak beslenir.
 */
$chapters = [
    ['tone' => '',           'sections' => [
        'partials/hero'         => ['hero' => $hero],
    ]],
    ['tone' => '',           'sections' => [
        'partials/stats'        => ['stats' => $stats],
        'partials/compare'      => [],
        'partials/scrub'        => [],
    ]],
    ['tone' => 'band-raise', 'sections' => [
        'partials/services'     => ['services' => $services],
        'partials/process'      => ['process' => $process, 'processSummary' => $processSummary],
        'partials/pricing'      => ['plans' => $plans],
    ]],
    ['tone' => 'band-deep',  'sections' => [
        'partials/manifesto'    => [],
    ]],
    ['tone' => '',           'sections' => [
        'partials/testimonials' => ['testimonials' => $testimonials],
        'partials/faq'          => ['faq' => $faq],
    ]],
    ['tone' => 'band-raise', 'sections' => [
        'partials/contact'      => ['brand' => $brand],
    ]],
];

foreach ($chapters as $chapter) {
    $govde = '';
    foreach ($chapter['sections'] as $template => $data) {
        $govde .= partial($template, $data);
    }

    // Tonu olmayan chapter sarmalayici istemez: bos <div> uretmiyoruz.
    echo $chapter['tone'] === ''
        ? $govde
        : '<div class="' . e($chapter['tone']) . '">' . $govde . '</div>';
}
