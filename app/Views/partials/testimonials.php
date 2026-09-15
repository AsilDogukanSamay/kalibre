<?php /** @var array<int,array<string,mixed>> $testimonials */ ?>
<section class="section shell">
    <div class="section-head">
        <span class="eyebrow">Müşteri geri bildirimi</span>
        <h2 class="h-section text-balance">Teslimde rapor veriyoruz, fark oradan başlıyor.</h2>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <?php foreach ($testimonials as $t): ?>
            <figure class="glass-card flex flex-col gap-4">
                <div class="flex gap-1" aria-label="<?= e((string) $t['rating']) ?> yıldız">
                    <?php for ($i = 0; $i < (int) $t['rating']; $i++): ?>
                        <svg class="star" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"/>
                        </svg>
                    <?php endfor; ?>
                </div>
                <blockquote class="text-base leading-relaxed text-ink">&ldquo;<?= e($t['quote']) ?>&rdquo;</blockquote>
                <figcaption class="mt-auto">
                    <span class="block text-sm  text-ink"><?= e($t['name']) ?></span>
                    <span class="block text-xs text-ink-faint"><?= e($t['role']) ?></span>
                </figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
