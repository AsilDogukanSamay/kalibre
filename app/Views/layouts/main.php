<?php
/** @var string $content */
/** @var array<string,mixed> $brand */
$brand = $brand ?? [];
$brandTheme = $brandTheme ?? App\Support\Brand::current();
?><!doctype html>
<html lang="tr" data-brand="<?= e($brandTheme['key']) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($brandTheme['name']) ?> · Boya düzeltme ve seramik kaplama, İstanbul</title>
    <meta name="description" content="Kalibre, İstanbul Maslak'ta boya düzeltme ve seramik kaplama atölyesi. Her araç mikron ölçümüyle başlar, ölçüm raporuyla teslim edilir.">
    <meta name="theme-color" content="#101214">
    <meta property="og:title" content="Kalibre · Boya düzeltme ve seramik kaplama">
    <meta property="og:description" content="Her araç mikron ölçümüyle başlar, ölçüm raporuyla teslim edilir.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= e(($_ENV['APP_URL'] ?? 'https://alanadiniz.com') . '/assets/img/og-kapak.jpg') ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>

<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[70] focus:rounded-glass focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:text-white">
    İçeriğe atla
</a>

<header class="nav-bar">
    <div class="shell nav-in">
        <a href="#top" class="shrink-0 no-underline"><?= partial($brandTheme['logo'], ['size' => 'h-8 w-8']) ?></a>

        <nav class="ml-auto hidden items-center gap-7 lg:flex" aria-label="Ana menü">
            <a href="#calismalar" class="nav-link">Çalışmalar</a>
            <a href="#hizmetler" class="nav-link">Hizmetler</a>
            <a href="#surec" class="nav-link">Süreç</a>
            <a href="#fiyat" class="nav-link">Fiyat</a>
            <a href="#sss" class="nav-link">SSS</a>
            <a href="/case" class="nav-link">Vaka çalışması</a>
        </nav>

        <a href="#iletisim" class="btn-primary btn-sm ml-auto lg:ml-6">Randevu al</a>
    </div>
</header>

<main id="main">
    <?= $content ?>
</main>

<footer class="border-t border-line py-9">
    <div class="shell flex flex-col gap-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <?= partial($brandTheme['logo'], ['size' => 'h-7 w-7']) ?>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-ink-muted">
                <a class="nav-link" href="tel:<?= e(str_replace(' ', '', (string) ($brand['phone'] ?? ''))) ?>"><?= e($brand['phone'] ?? '') ?></a>
                <span><?= e($brand['hours'] ?? '') ?></span>
            </div>
        </div>
        <p class="body-sm max-w-3xl text-ink-faint"><?= e($brand['address'] ?? '') ?></p>
        <?php if ($brandTheme['disclaimer']): ?>
            <p class="glass-soft max-w-4xl p-4 text-xs leading-relaxed text-ink-faint">
                <strong class="text-ink-muted">Bilgilendirme:</strong>
                Bu sayfa teknik yetkinlik değerlendirmesi için hazırlanmış bağımsız bir prototiptir.
                <?= e($brandTheme['legal']) ?> ile ticari veya kurumsal bir bağlantısı yoktur; marka adı,
                amblem ve renkler yalnızca kurumsal kimliğe sadık arayüz tasarımını göstermek amacıyla
                kullanılmıştır. Tüm marka hakları <?= e($brandTheme['legal']) ?>'ye aittir.
            </p>
        <?php endif; ?>
    </div>
</footer>

<?= partial('partials/whatsapp', ['brand' => $brand]) ?>

<div class="toast-layer" id="toastLayer" role="status" aria-live="polite"></div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
