<?php /** @var array<int,array<string,mixed>> $services */ ?>
<section id="hizmetler" class="section shell">
    <div class="section-head">
        <h2 class="h-section text-balance">Dört iş yapıyoruz, dördünü de sonuna kadar.</h2>
        <p class="body-text">Araç kabul edilmeden önce hangi işlemin gerektiğine ölçüm karar verir. Gerekmeyen işlemi satmıyoruz.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-12">
        <?php foreach ($services as $service): ?>
            <?php
                $span = $service['size'] === 'lg' ? 'lg:col-span-7' : 'lg:col-span-5';
                // Dosya yoksa kirik gorsel ikonu yerine renk katmanina dusulur.
                $hasImg = !empty($service['image']) && asset_exists('img/' . $service['image']);
            ?>
            <article class="cell min-h-[22rem] <?= e($span) ?>">

                <?php if ($hasImg): ?>
                    <img class="cell-photo" src="<?= e(asset('img/' . $service['image'])) ?>" alt=""
                         loading="lazy" decoding="async">
                    <span class="cell-scrim"></span>
                <?php else: ?>
                    <span class="cell-tint"></span>
                <?php endif; ?>

                <div class="cell-body">
                    <h3 class="h-card"><?= e($service['title']) ?></h3>
                    <p class="body-sm max-w-md"><?= e($service['body']) ?></p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="cell-meta"><?= e($service['meta']) ?></span>
                        <?php if (!empty($service['price'])): ?>
                            <span class="cell-price"><?= e($service['price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
