<?php
/**
 * Vaka çalışması sayfası.
 * @var array<string,mixed> $intro @var array $problem @var array $decisions
 * @var array $metrics @var array $scale @var array $architecture @var array $next
 * @var array<string,mixed> $brandTheme @var array<string,array<string,mixed>> $brands
 */
?>
<section class="shell pb-6 pt-14 sm:pt-20">
    <div class="flex max-w-3xl flex-col gap-5">
        <span class="text-xs uppercase tracking-[0.16em] text-brand">Vaka çalışması</span>
        <h1 class="h-display text-balance">Ölçülebilir bir hizmeti ölçülebilir bir arayüze çevirmek.</h1>
        <p class="body-text"><?= e($intro['summary']) ?></p>
    </div>

    <dl class="mt-10 grid gap-px overflow-hidden rounded-glass border border-line sm:grid-cols-3">
        <?php foreach ([['Rol', $intro['role']], ['Süre', $intro['duration']], ['Teknoloji', $intro['stack']]] as [$k, $v]): ?>
            <div class="bg-surface-800 p-5">
                <dt class="text-[11px] uppercase tracking-[0.14em] text-ink-faint"><?= e($k) ?></dt>
                <dd class="mt-1.5 text-sm text-ink"><?= e($v) ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>

<!-- Ölçülen sonuçlar -->
<section class="shell pt-12">
    <h2 class="h-section mb-8">Ölçülen sonuçlar</h2>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($metrics as $m): ?>
            <div class="glass-card flex flex-col gap-2">
                <span class="readout text-[2.5rem] leading-none text-brand"><?= e($m['value']) ?></span>
                <span class="text-sm text-ink"><?= e($m['label']) ?></span>
                <p class="body-sm text-xs"><?= e($m['note']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Problem -->
<section class="shell section">
    <div class="section-head">
        <h2 class="h-section">Başlangıç noktası</h2>
        <p class="body-text">Atölye sahibiyle konuşulduğunda üç sorun tekrar ediyor.</p>
    </div>
    <ol class="grid gap-4 md:grid-cols-3">
        <?php foreach ($problem as $i => $p): ?>
            <li class="glass-card flex flex-col gap-2.5">
                <span class="readout text-sm text-ink-faint"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                <h3 class="h-card"><?= e($p['title']) ?></h3>
                <p class="body-sm"><?= e($p['body']) ?></p>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<!-- Kararlar -->
<section class="shell section pt-0">
    <div class="section-head">
        <h2 class="h-section">Tasarım kararları</h2>
        <p class="body-text">Her kararın bir gerekçesi ve bir bedeli var. İkisi birlikte yazılmazsa karar değil, tercih olur.</p>
    </div>

    <div class="flex flex-col gap-4">
        <?php foreach ($decisions as $d): ?>
            <article class="glass-card grid gap-5 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <span class="readout text-sm text-brand"><?= e($d['n']) ?></span>
                    <h3 class="h-card mt-2"><?= e($d['title']) ?></h3>
                </div>
                <div class="flex flex-col gap-4 lg:col-span-8">
                    <p class="text-[0.95rem] leading-relaxed text-ink"><?= e($d['decision']) ?></p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="border-l-2 border-brand pl-4">
                            <span class="block text-[11px] uppercase tracking-[0.14em] text-ink-faint">Neden</span>
                            <p class="body-sm mt-1"><?= e($d['why']) ?></p>
                        </div>
                        <div class="border-l-2 border-line-strong pl-4">
                            <span class="block text-[11px] uppercase tracking-[0.14em] text-ink-faint">Bedeli</span>
                            <p class="body-sm mt-1"><?= e($d['tradeoff']) ?></p>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Tasarım sistemi -->
<section class="shell section pt-0">
    <div class="section-head">
        <h2 class="h-section">Tasarım sistemi</h2>
        <p class="body-text">Aşağıdaki değerler ekran görüntüsü değil. Sayfanın gerçekten kullandığı CSS değişkenlerinden okunuyor.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-12">

        <div class="glass-card lg:col-span-5">
            <h3 class="h-card mb-4">Marka teması</h3>
            <p class="body-sm mb-5">Renkler CSS değişkenlerinde. Tema değiştirmek tek bir <span class="readout text-ink">.env</span> satırı; hiçbir sınıf veya şablon değişmiyor.</p>

            <div class="flex flex-col gap-3">
                <?php foreach ($brands as $key => $b): ?>
                    <div class="flex items-center gap-3 rounded-glass border border-line p-3 <?= $key === $brandTheme['key'] ? 'border-brand bg-brand/[0.07]' : '' ?>">
                        <div class="flex gap-1.5">
                            <?php foreach ($b['palette'] as $hex): ?>
                                <span class="h-7 w-7 rounded-glass border border-white/15" data-swatch="<?= e($hex) ?>"></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm text-ink"><?= e($b['name']) ?></span>
                            <span class="readout block text-xs text-ink-faint">APP_BRAND=<?= e($key) ?></span>
                        </div>
                        <?php if ($key === $brandTheme['key']): ?>
                            <span class="ml-auto rounded-pill bg-brand px-2.5 py-0.5 text-[11px] text-white">aktif</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="glass-card lg:col-span-7">
            <h3 class="h-card mb-4">Tipografi: tek aile, genişlik ekseni</h3>
            <p class="body-sm mb-5">İkinci bir yazı tipi yerine Archivo'nun genişlik ekseni ikinci ses olarak kullanılıyor. Aynı aile, üç farklı karakter.</p>

            <div class="flex flex-col divide-y divide-line">
                <?php foreach ($scale as $s): ?>
                    <div class="flex items-baseline gap-5 py-3.5">
                        <span class="w-24 shrink-0 text-[11px] uppercase tracking-[0.14em] text-ink-faint"><?= e($s['token']) ?></span>
                        <span class="flex-1 truncate text-2xl text-ink <?= $s['token'] === 'display' ? 'display' : ($s['token'] === 'readout' ? 'readout' : '') ?>">138 µm</span>
                        <span class="readout hidden text-xs text-ink-faint sm:block">wdth <?= e($s['wdth']) ?> · wght <?= e($s['wght']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="glass-card lg:col-span-12">
            <h3 class="h-card mb-4">Cam yüzey ölçeği</h3>
            <p class="body-sm mb-5">Üç yoğunluk. Her buzlu yüzey bu üçünden türüyor, yeni kural yazılmıyor.</p>
            <div class="grid gap-4 sm:grid-cols-3">
                <?php foreach ([['glass-soft', 'En hafif', 'Etiketler, rozetler, ikincil paneller'], ['glass', 'Standart', 'Kartlar, bölüm yüzeyleri'], ['glass-strong', 'En güçlü', 'Form, toast, hero bilgi paneli']] as [$cls, $ad, $kullanim]): ?>
                    <div class="<?= e($cls) ?> glass-sheen p-5">
                        <span class="readout block text-sm text-ink"><?= e($cls) ?></span>
                        <span class="mt-1 block text-xs text-ink-muted"><?= e($ad) ?></span>
                        <p class="body-sm mt-3 text-xs"><?= e($kullanim) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<!-- Mimari -->
<section class="shell section pt-0">
    <div class="section-head">
        <h2 class="h-section">İstek nasıl akıyor</h2>
        <p class="body-text">Her katmanın tek bir sorumluluğu var. Sorgu yalnızca modelde, kaçış yalnızca görünümde, yapılandırma yalnızca Env'de.</p>
    </div>

    <ol class="flex flex-col gap-2">
        <?php foreach ($architecture as $i => $a): ?>
            <li class="glass-soft flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:gap-6">
                <span class="readout w-8 shrink-0 text-xs text-brand"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                <span class="readout w-56 shrink-0 text-sm text-ink"><?= e($a['layer']) ?></span>
                <span class="body-sm text-xs"><?= e($a['role']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<!-- Sonraki adımlar -->
<section class="shell section pt-0">
    <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">
        <div class="lg:col-span-4">
            <h2 class="h-section">Sonraki adımlar</h2>
            <p class="body-text mt-3">Bitmiş bir ürün değil, doğrulanmış bir ilk sürüm.</p>
        </div>
        <ul class="flex flex-col gap-3 lg:col-span-8">
            <?php foreach ($next as $n): ?>
                <li class="glass-soft flex gap-3 p-4">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-pill bg-brand"></span>
                    <span class="body-sm text-ink-muted"><?= e($n) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="shell pb-20">
    <div class="glass-strong glass-sheen flex flex-col items-start gap-5 p-7 sm:p-10">
        <h2 class="h-card">Ürünü canlı görmek için</h2>
        <p class="body-text">Kararların arayüzde nasıl karşılık bulduğunu sayfanın kendisinde incele.</p>
        <a href="/" class="btn-primary">Landing page'e dön</a>
    </div>
</section>
