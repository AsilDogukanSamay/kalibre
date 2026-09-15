<?php /** @var string $campaignEndsAt */ ?>
<?php if (($campaignEndsAt ?? '') !== ''): ?>
<div class="glass-soft w-full max-w-md p-4" data-countdown="<?= e($campaignEndsAt) ?>">
    <p class="mb-3 text-xs  uppercase tracking-[0.14em] text-brand-text">
        Kış bakım kampanyası &middot; seramik kaplamada yüzde 20 indirim
    </p>
    <div class="grid grid-cols-4 gap-2" data-countdown-cells>
        <div class="countdown-cell"><span class="countdown-num" data-cd="days">--</span><span class="countdown-lbl">gün</span></div>
        <div class="countdown-cell"><span class="countdown-num" data-cd="hours">--</span><span class="countdown-lbl">saat</span></div>
        <div class="countdown-cell"><span class="countdown-num" data-cd="minutes">--</span><span class="countdown-lbl">dakika</span></div>
        <div class="countdown-cell"><span class="countdown-num" data-cd="seconds">--</span><span class="countdown-lbl">saniye</span></div>
    </div>
    <p class="mt-3 hidden text-sm text-ink-muted" data-countdown-expired>Kampanya sona erdi. Güncel fiyat için bize ulaşın.</p>
</div>
<?php endif; ?>
