<?php
/** @var string $content */
/** @var array<string,mixed> $brand */
$brand       = $brand ?? [];
$brandTheme  = $brandTheme ?? App\Support\Brand::current();
$siteUrl     = rtrim((string) App\Core\Env::get('APP_URL', 'http://localhost:5174'), '/');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$nonce       = App\Core\Security::nonce();
$legalPages  = App\Support\LegalContent::pages();

// Sayfaya ozel baslik/aciklama verilmediyse ana sayfa metni kullanilir.
$pageTitle = isset($pageTitle)
    ? $pageTitle . ' · ' . $brandTheme['name']
    // Marka once: sekme daraldiginda once o kirpilir. Ardindan jenerik bir
    // kategori degil sayfanin kendi iddiasi gelir; 65 karakterden 47'ye indi.
    : $brandTheme['name'] . ' · Boyayı ölçerek düzeltiyoruz';

// Meta aciklamasi hero alt yazisindan TURER. Onceki halde ayni cumle hem
// burada hem SiteContent icinde yaziliydi; birini duzeltip digerini unutmak
// an meselesiydi. Tek kaynak: SiteContent.
$pageDescription = $pageDescription
    ?? $brandTheme['name'] . ', ' . App\Support\SiteContent::all()['hero']['subtitle'];
?><!doctype html>
<html lang="tr" data-brand="<?= e($brandTheme['key']) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#101214">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= e($siteUrl . '/assets/img/og-kapak.jpg') ?>">
    <meta property="og:url" content="<?= e($siteUrl . $currentPath) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= e($siteUrl . $currentPath) ?>">

    <link rel="icon" type="image/svg+xml" href="<?= e(asset($brandTheme['favicon'])) ?>">
    <link rel="apple-touch-icon" href="<?= e(asset($brandTheme['favicon'])) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>

<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[70] focus:rounded-glass focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:text-white">
    İçeriğe atla
</a>

<header class="nav-bar" data-nav>
    <div class="shell nav-in">
        <a href="/" class="shrink-0 no-underline"><?= partial($brandTheme['logo'], ['size' => 'h-8 w-8']) ?></a>

        <nav class="ml-auto hidden items-center gap-7 lg:flex" aria-label="Ana menü">
            <a href="/#calismalar" class="nav-link">Çalışmalar</a>
            <a href="/#hizmetler" class="nav-link">Hizmetler</a>
            <a href="/#surec" class="nav-link">Süreç</a>
            <a href="/#fiyat" class="nav-link">Fiyat</a>
            <a href="/#sss" class="nav-link">SSS</a>
            <a href="/case" class="nav-link">Vaka çalışması</a>
        </nav>

        <a href="/#iletisim" class="btn-primary btn-sm ml-auto lg:ml-6">Randevu al</a>
    </div>

    <!-- Okuma ilerlemesi. Dekoratif bir cizgi degil: sayfanin dili olcum,
         bu da okunan mesafenin olcumu. Rengi olcum skalasindan (accent)
         geliyor, marka kirmizisindan degil - kirmizi eylem rengi olarak
         butonlarda kaliyor. Genislik tek kurallik stylesheet uzerinden
         tasinan --okuma-p degiskeninden okunur, style attribute'u yok. -->
    <span class="nav-progress" aria-hidden="true"></span>
</header>

<main id="main">
    <?= $content ?>
</main>

<footer class="foot">
    <div class="shell flex flex-col gap-8">

        <div class="foot-grid">
            <div class="flex flex-col gap-3">
                <?= partial($brandTheme['logo'], ['size' => 'h-7 w-7']) ?>
                <p class="body-sm max-w-[34ch]"><?= e($brand['address'] ?? '') ?></p>
                <a class="foot-harita" href="<?= e($brand['maps'] ?? '#') ?>" target="_blank" rel="noopener">Haritada gör</a>
            </div>

            <div class="flex flex-col gap-2">
                <span class="foot-key">İletişim</span>
                <a class="foot-tel" href="tel:<?= e(str_replace(' ', '', (string) ($brand['phone'] ?? ''))) ?>"><?= e($brand['phone'] ?? '') ?></a>
                <span class="body-sm"><?= e($brand['hours'] ?? '') ?></span>
            </div>

            <nav class="flex flex-col gap-2" aria-label="Yasal">
                <span class="foot-key">Bilgi</span>
                <?php foreach ($legalPages as $legalPage): ?>
                    <a class="nav-link" href="<?= e($legalPage['path']) ?>"><?= e($legalPage['nav']) ?></a>
                <?php endforeach; ?>
                <a class="nav-link" href="/case">Vaka çalışması</a>
            </nav>
        </div>

        <?php if ($brandTheme['disclaimer']): ?>
            <p class="note-box max-w-[78ch]">
                <strong class="text-ink-muted">Bilgilendirme:</strong>
                Bu sayfa teknik yetkinlik değerlendirmesi için hazırlanmış bağımsız bir prototiptir.
                <?= e($brandTheme['legal']) ?> ile ticari veya kurumsal bir bağlantısı yoktur; marka adı,
                amblem ve renkler yalnızca kurumsal kimliğe sadık arayüz tasarımını göstermek amacıyla
                kullanılmıştır. Tüm marka hakları <?= e($brandTheme['legal']) ?>'ye aittir.
                Sayfadaki atölye bilgileri, çalışma görselleri ve referanslar temsilidir.
            </p>
        <?php endif; ?>

        <p class="foot-telif">
            &copy; <?= e(date('Y')) ?> <?= e($brandTheme['name']) ?> &middot; İstanbul Maslak
        </p>
    </div>
</footer>

<?= partial('partials/whatsapp', ['brand' => $brand]) ?>

<div class="toast-layer" id="toastLayer" role="status" aria-live="polite"></div>

<div class="cursor" id="cursor" aria-hidden="true"><span class="cursor-ring"></span></div>

<!-- Schema.org LocalBusiness: adres, telefon ve calisma saatlerinin
     arama sonucunda zengin sonuc olarak cikabilmesi icin.
     nonce: CSP script-src satir ici bloklari nonce ile kabul eder. -->
<script type="application/ld+json" nonce="<?= e($nonce) ?>">
<?= json_encode([
    '@context'  => 'https://schema.org',
    '@type'     => 'AutoDetailing',
    'name'      => $brandTheme['name'],
    'url'       => $siteUrl,
    'image'     => $siteUrl . '/assets/img/og-kapak.jpg',
    'telephone' => $brand['phone'] ?? '',
    'address'   => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Ayazağa Mah. Kemerburgaz Cad. No 14',
        'addressLocality' => 'Sarıyer',
        'addressRegion'   => 'İstanbul',
        'addressCountry'  => 'TR',
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => $brand['geo']['lat'] ?? null,
        'longitude' => $brand['geo']['lng'] ?? null,
    ],
    'openingHoursSpecification' => [
        ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'], 'opens' => '09:00', 'closes' => '19:00'],
        ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Saturday'], 'opens' => '10:00', 'closes' => '16:00'],
    ],
    'priceRange' => '₺₺₺',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
