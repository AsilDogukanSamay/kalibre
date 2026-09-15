<?php /** @var array<int,array<string,mixed>> $plans */ ?>
<section id="fiyat" class="section shell">
    <div class="section-head">
        <h2 class="h-section text-balance">İki paket, ikisi de ölçümle başlıyor.</h2>
        <p class="body-text">Fiyatlar orta sınıf sedan içindir. SUV ve ticari araçlarda yüzey alanına göre fark uygulanır, ölçüm sonrası net fiyat verilir.</p>
        <p class="body-sm">İç detaylı temizlik ve şeffaf koruma filmi paket dışında, tek tek de alınabilir. Başlangıç fiyatları hizmetler bölümünde.</p>
        <p class="text-xs text-ink-faint">Son güncelleme: Eylül 2026</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <?php foreach ($plans as $plan): ?>
            <article class="glass-card flex flex-col gap-5 <?= $plan['featured'] ? 'border-brand/40 bg-brand/[0.07]' : '' ?>">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="h-card"><?= e($plan['name']) ?></h3>
                    <?php if ($plan['featured']): ?>
                        <span class="rounded-full bg-brand px-3 py-1 text-xs  text-white">En çok tercih edilen</span>
                    <?php endif; ?>
                </div>

                <p class="body-sm"><?= e($plan['intro']) ?></p>

                <p class="flex items-baseline gap-1.5">
                    <span class="text-4xl  tabular-nums tracking-tight text-ink"><?= e($plan['price']) ?></span>
                    <span class="text-sm text-ink-muted"><?= e($plan['currency']) ?></span>
                </p>

                <ul class="flex flex-col gap-2.5">
                    <?php foreach ($plan['items'] as $item): ?>
                        <li class="body-sm flex gap-2.5">
                            <svg class="mt-1 h-3.5 w-3.5 shrink-0 text-brand-text" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 0 1 1.4-1.4l3.8 3.8 6.8-6.8a1 1 0 0 1 1.4 0z" clip-rule="evenodd"/>
                            </svg>
                            <span><?= e($item) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="#iletisim" class="<?= $plan['featured'] ? 'btn-primary' : 'btn-ghost' ?> mt-auto w-fit">Randevu al</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
