<?php /** @var string $size */ $size = $size ?? 'h-8 w-8'; ?>
<span class="inline-flex items-center gap-2.5">
    <svg class="<?= e($size) ?> shrink-0 text-brand" viewBox="0 0 24 24" role="img" aria-label="Kalibre">
        <circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="1.4"/>
        <path d="M12 3v3M12 18v3M3 12h3M18 12h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        <circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4"/>
    </svg>
    <span class="flex flex-col leading-none">
        <span class="wordmark">KALİBRE</span>
        <span class="wordmark-sub">Detailing Studio</span>
    </span>
</span>
