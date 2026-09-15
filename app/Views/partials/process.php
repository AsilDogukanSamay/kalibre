<?php /** @var array<int,array<string,string>> $process */ ?>
<section id="surec" class="section shell">
    <div class="section-head">
        <h2 class="h-section text-balance">Araç girdiğinde ne oluyor?</h2>
        <p class="body-text">Dört aşama. Her aşamanın çıktısı yazılı, işlem sonunda tek dosya halinde size veriliyor.</p>
    </div>

    <ol class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($process as $step): ?>
            <li class="glass-card flex flex-col gap-2">
                <span class="h-0.5 w-8 bg-brand"></span>
                <h3 class="mt-1 text-lg  tracking-tight"><?= e($step['title']) ?></h3>
                <p class="body-sm"><?= e($step['body']) ?></p>
                <span class="mt-auto pt-3 text-sm  tabular-nums text-brand"><?= e($step['time']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>
