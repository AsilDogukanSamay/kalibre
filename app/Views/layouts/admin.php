<?php
/**
 * Yonetim paneli duzeni.
 *
 * Pazarlama duzeninden ayridir: WhatsApp butonu, footer ve Schema.org
 * blogu burada yoktur. Ayni tasarim sistemini (ayni CSS dosyasini) kullanir,
 * yani panel de sitenin dilini konusur.
 *
 * @var string $content
 * @var string|null $adminUser
 */
$brandTheme = $brandTheme ?? App\Support\Brand::current();
$pageTitle  = ($pageTitle ?? 'Yönetim') . ' · ' . $brandTheme['name'];
$csrf       = App\Core\Session::csrfToken();
$adminUser  = $adminUser ?? null;
?><!doctype html>
<html lang="tr" data-brand="<?= e($brandTheme['key']) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?></title>
    <meta name="theme-color" content="#101214">
    <link rel="icon" type="image/svg+xml" href="<?= e(asset($brandTheme['favicon'])) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>

<header class="nav-bar">
    <div class="shell nav-in">
        <a href="/" class="shrink-0 no-underline"><?= partial($brandTheme['logo'], ['size' => 'h-8 w-8']) ?></a>
        <span class="ml-4 text-sm text-ink-faint">Yönetim</span>

        <?php if ($adminUser !== null): ?>
            <div class="ml-auto flex items-center gap-4">
                <span class="hidden text-sm text-ink-muted sm:inline"><?= e($adminUser) ?></span>
                <form method="post" action="/yonetim/cikis">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button class="btn-ghost btn-sm" type="submit">Çıkış</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</header>

<main id="main">
    <?= $content ?>
</main>

</body>
</html>
