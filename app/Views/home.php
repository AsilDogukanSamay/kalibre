<?php
/**
 * Ana sayfa bolumleri. Her bolum kendi parcasindan gelir,
 * icerik SiteContent sinifindan tek kaynak olarak beslenir.
 */
$sections = [
    'partials/hero'         => ['hero' => $hero, 'campaignEndsAt' => $campaignEndsAt],
    'partials/stats'        => ['stats' => $stats],
    'partials/compare'      => [],
    'partials/scrub'        => [],
    'partials/services'     => ['services' => $services],
    'partials/process'      => ['process' => $process, 'processSummary' => $processSummary],
    'partials/pricing'      => ['plans' => $plans],
    'partials/testimonials' => ['testimonials' => $testimonials],
    'partials/faq'          => ['faq' => $faq],
    'partials/contact'      => ['brand' => $brand],
];

foreach ($sections as $template => $data) {
    echo partial($template, $data);
}
