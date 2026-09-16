<?php
/**
 * Kalibre amblemi. Atolyenin kendi markasi oldugu icin amblem de kelime
 * markasi da projeye ait; ikisi de vektor.
 *
 * Bosch temasindaki gibi burada da kelime markasi bir dosya ile
 * degistirilebilir: public/assets/img/wordmark-kalibre.svg varsa o kullanilir.
 *
 * @var string $size
 */
$size     = $size ?? 'h-8 w-8';
$wordmark = 'img/wordmark-kalibre.svg';
?>
<span class="inline-flex items-center gap-2.5">
    <svg class="<?= e($size) ?> shrink-0 text-brand-text" viewBox="0 0 24 24" role="img" aria-label="Kalibre">
        <circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="1.4"/>
        <path d="M12 3v3M12 18v3M3 12h3M18 12h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        <circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4"/>
    </svg>
    <?php if (asset_exists($wordmark)): ?>
        <img class="h-5 w-auto shrink-0" src="<?= e(asset($wordmark)) ?>" alt="Kalibre Detailing Studio" width="120" height="20">
    <?php else: ?>
        <span class="flex flex-col leading-none">
            <span class="wordmark">KALİBRE</span>
            <span class="wordmark-sub">Detailing Studio</span>
        </span>
    <?php endif; ?>
</span>
