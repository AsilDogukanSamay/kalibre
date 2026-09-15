<?php /** @var array<string,mixed> $hero @var string $campaignEndsAt */ ?>
<section id="top" class="relative overflow-hidden border-b border-line">

    <div class="hero-bg" id="heroBg">
        <img class="hero-media" src="<?= e(asset('img/hero-poster.webp')) ?>" alt="" aria-hidden="true"
             width="1920" height="1080" fetchpriority="high" decoding="async">
        <video class="hero-media hero-video" id="heroVideo"
               data-src="<?= e(asset('video/hero.mp4')) ?>"
               poster="<?= e(asset('img/hero-poster.webp')) ?>"
               muted loop playsinline preload="none" aria-hidden="true" tabindex="-1"></video>
        <span class="hero-scrim"></span>
    </div>

    <div class="shell hero-inner grid items-center gap-10 py-16 lg:min-h-[calc(100dvh-4rem)] lg:grid-cols-12 lg:gap-14 lg:py-24">

        <div class="hero-copy flex flex-col items-start gap-7 lg:col-span-7">
            <h1 class="h-display animate-rise-1 opacity-0"><?= e($hero['title']) ?></h1>
            <p class="body-text animate-rise-2 opacity-0"><?= e($hero['subtitle']) ?></p>

            <div class="flex flex-wrap gap-3 animate-rise-3 opacity-0">
                <a href="<?= e($hero['primary']['href']) ?>" class="btn-primary"><?= e($hero['primary']['label']) ?></a>
                <a href="<?= e($hero['secondary']['href']) ?>" class="btn-ghost"><?= e($hero['secondary']['label']) ?></a>
            </div>

            <?= partial('partials/countdown', ['campaignEndsAt' => $campaignEndsAt]) ?>
        </div>

        <div class="lg:col-span-5">
            <div class="glass-strong glass-sheen flex flex-col gap-4 p-5">
                <p class="text-xs uppercase tracking-[0.14em] text-brand">Son işlemden ölçüm</p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="glass-soft px-4 py-3">
                        <span class="readout block text-2xl text-ink">138 &rarr; 129</span>
                        <span class="mt-1 block text-[10px] uppercase tracking-[0.14em] text-ink-faint">mikron, kaput</span>
                    </div>
                    <div class="glass-soft px-4 py-3">
                        <span class="readout block text-2xl text-ink">41 &rarr; 94</span>
                        <span class="mt-1 block text-[10px] uppercase tracking-[0.14em] text-ink-faint">parlaklık, GU</span>
                    </div>
                </div>

                <p class="body-sm">Her araç 11 noktadan ölçülür. Kalan vernik güvenli sınırın altına inecekse ikinci pasoya geçmiyoruz.</p>
            </div>
        </div>

    </div>
</section>
