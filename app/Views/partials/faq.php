<?php /** @var array<int,array<string,string>> $faq */ ?>
<section id="sss" class="section shell">
    <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">
        <div class="section-head mb-0 lg:col-span-4" data-reveal>
            <span class="eyebrow-label">Sık sorulanlar</span>
            <h2 class="h-section text-balance" data-satir>Merak edilenler</h2>
            <p class="body-text">Ölçümden önce en çok sorulan dört soru. Cevaplar kısa; ayrıntısını telefonda konuşuyoruz.</p>
        </div>

        <div class="flex flex-col gap-3 lg:col-span-8" data-reveal-group>
            <?php foreach ($faq as $i => $item): ?>
                <details class="faq-item group" data-reveal data-faq="<?= e((string) $i) ?>">
                    <summary class="faq-summary">
                        <span><?= e($item['q']) ?></span>
                        <svg class="faq-icon group-open:rotate-45" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9 4h2v12H9z"/><path d="M4 9h12v2H4z"/>
                        </svg>
                    </summary>
                    <div class="faq-panel">
                        <p class="faq-body"><?= e($item['a']) ?></p>
                    </div>
                </details>
            <?php endforeach; ?>
            <p class="body-sm mt-3">
                Aradığınız soruyu bulamadıysanız
                <a class="text-brand-text underline decoration-brand/40 underline-offset-4 transition hover:decoration-brand" href="#iletisim">formu bırakın</a>,
                aynı gün içinde arıyoruz.
            </p>
        </div>
    </div>
</section>
