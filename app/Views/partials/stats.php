<?php /** @var array<int,array<string,string>> $stats */ ?>
<section class="shell">
    <div class="glass grid gap-px overflow-hidden sm:grid-cols-3" data-reveal>
        <?php foreach ($stats as $stat): ?>
            <div class="bg-white/[0.02] p-6 sm:p-7">
                <span class="stat-num block text-ink"><?= e($stat['value']) ?></span>
                <span class="body-sm mt-1.5 block"><?= e($stat['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
