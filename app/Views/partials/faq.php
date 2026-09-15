<?php /** @var array<int,array<string,string>> $faq */ ?>
<section id="sss" class="section shell">
    <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">
        <div class="lg:col-span-4">
            <h2 class="h-section text-balance">Sık sorulanlar</h2>
        </div>

        <div class="flex flex-col gap-3 lg:col-span-8">
            <?php foreach ($faq as $item): ?>
                <details class="glass group p-5 transition hover:border-white/20">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-base  text-ink">
                        <span><?= e($item['q']) ?></span>
                        <svg class="h-5 w-5 shrink-0 text-brand transition group-open:rotate-45" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9 4h2v12H9z"/><path d="M4 9h12v2H4z"/>
                        </svg>
                    </summary>
                    <p class="body-sm mt-3 max-w-2xl"><?= e($item['a']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
