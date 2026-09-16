<?php /** @var array<string,mixed> $hero @var string $campaignEndsAt */ ?>
<section id="top" class="relative overflow-hidden border-b border-line">

    <div class="hero-bg" id="heroBg">
        <!-- Poster iki kirpimda. Onceki halde 1920 genislikteki dosya 390px'lik
             bir telefona da iniyordu: ekranin gosterebileceginin bes kati veri.
             Dar ekranda 960 genislikteki surum iniyor, 124 KB yerine 27 KB. -->
        <picture>
            <source media="(min-width: 640px)" srcset="<?= e(asset('img/hero-poster.webp')) ?>" width="1920" height="1080">
            <img class="hero-media" src="<?= e(asset('img/hero-poster-dar.webp')) ?>" alt="" aria-hidden="true"
                 width="960" height="540" fetchpriority="high" decoding="async">
        </picture>
        <!-- poster niteligi bilerek YOK. Tarayici poster'i preload="none" olsa
             bile indiriyor; olculdu: mobilde 121 KB'lik dosya her yuklemede
             iniyordu. Ustelik hic gorunmuyor, cunku video oynayana kadar
             saydam ve arkasinda zaten <picture> duruyor. -->
        <video class="hero-media hero-video" id="heroVideo"
               data-src="<?= e(asset('video/hero.mp4')) ?>"
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
                <p class="eyebrow-label">Son işlemden ölçüm</p>

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
