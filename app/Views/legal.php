<?php
/**
 * Yasal metin sablonu. Icerik LegalContent sinifindan gelir; bu dosya
 * yalnizca yerlesimi bilir, tek bir cumle icermez.
 *
 * @var array<string,mixed> $page
 * @var array<string,array<string,string>> $legalPages
 */
?>
<article class="section shell">
    <header class="legal-head">
        <p class="eyebrow-label">Yasal bilgilendirme</p>
        <h1 class="h-section mt-3"><?= e($page['title']) ?></h1>
        <p class="body-text mt-4"><?= e($page['lead']) ?></p>
        <p class="field-hint mt-4">
            Son güncelleme: <?= e($page['updated']) ?> · Metin sürümü: <?= e($page['version']) ?>
        </p>
    </header>

    <div class="legal-body">
        <?php foreach ($page['sections'] as $section): ?>
            <section class="legal-section">
                <h2 class="legal-h"><?= e($section['heading']) ?></h2>

                <?php foreach ($section['body'] ?? [] as $paragraph): ?>
                    <p class="legal-p"><?= e($paragraph) ?></p>
                <?php endforeach; ?>

                <?php if (!empty($section['note'])): ?>
                    <p class="legal-note"><?= e($section['note']) ?></p>
                <?php endif; ?>

                <?php if (!empty($section['list'])): ?>
                    <ul class="legal-list">
                        <?php foreach ($section['list'] as $item): ?>
                            <li class="legal-li"><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <footer class="legal-foot">
        <p class="body-sm">
            Diğer metin:
            <?php foreach ($legalPages as $other): ?>
                <?php if ($other['path'] !== ($page['path'] ?? '')): ?>
                    <a class="legal-link" href="<?= e($other['path']) ?>"><?= e($other['nav']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </p>
        <a class="btn-ghost btn-sm w-fit" href="/">Ana sayfaya dön</a>
    </footer>
</article>
