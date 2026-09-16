<?php
/**
 * Bosch Car Service amblemi.
 *
 * AMBLEM (sol taraf) ozgun vektordur: Bosch'un armatur/capa sembolunun
 * orijinal path verisi korunmustur. Renk currentColor ile tasarim
 * sisteminden yonetilir, boylece tema degisince amblem de doner.
 * Kaynak: Simple Icons (CC0 ikon dosyasi).
 *
 * KELIME MARKASI (sag taraf) iki kaynaktan gelebilir:
 *   1. public/assets/img/wordmark-bosch.svg dosyasi varsa o kullanilir.
 *      Lisansli resmi varlik bu dosyaya birakildiginda sablon, sinif veya
 *      bilesen degismeden devreye girer.
 *   2. Dosya yoksa tipografik kilit devreye girer (Archivo). Bosch Sans
 *      lisansli bir yazi tipi oldugu icin prototipte yerine bu kullanilir.
 *
 * Marka Robert Bosch GmbH tescillidir; bu bagimsiz bir prototiptir.
 *
 * @var string $size
 */
$size     = $size ?? 'h-8 w-8';
$wordmark = 'img/wordmark-bosch.svg';
?>
<span class="inline-flex items-center gap-2.5">
    <svg class="<?= e($size) ?> shrink-0 text-brand-text" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Bosch">
        <path fill="currentColor" d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12C23.996 5.374 18.626.004 12 0zm0 22.88C5.991 22.88 1.12 18.009 1.12 12S5.991 1.12 12 1.12 22.88 5.991 22.88 12c-.006 6.006-4.874 10.874-10.88 10.88zm4.954-18.374h-.821v4.108h-8.24V4.506h-.847a8.978 8.978 0 0 0 0 14.988h.846v-4.108h8.24v4.108h.822a8.978 8.978 0 0 0 0-14.988zM6.747 17.876a7.86 7.86 0 0 1 0-11.752v11.752zm9.386-3.635h-8.24V9.734h8.24v4.507zm1.12 3.61V6.124a7.882 7.882 0 0 1 0 11.727z"/>
    </svg>

    <?php if (asset_exists($wordmark)): ?>
        <img class="h-5 w-auto shrink-0" src="<?= e(asset($wordmark)) ?>" alt="Bosch Car Service" width="120" height="20">
    <?php else: ?>
        <span class="flex flex-col leading-none">
            <span class="wordmark">BOSCH</span>
            <span class="wordmark-sub">Car Service</span>
        </span>
    <?php endif; ?>
</span>
