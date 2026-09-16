<!-- Bolume baglam veren giris. Sag sutun, asagida degisecek olan
     olcumlerin baslangic ve bitis degerlerini onceden gosterir:
     boslugu doldurmak icin degil, beklentiyi kurmak icin. -->
<section class="shell pb-8 pt-4">
    <div class="grid items-end gap-8 lg:grid-cols-12 lg:gap-14">
        <div class="section-head mb-0 max-w-none lg:col-span-7" data-reveal>
            <span class="eyebrow-label">Canlı ölçüm</span>
            <h2 class="h-section text-balance" data-satir>Pasoyu kendi hızınızda izleyin.</h2>
            <p class="body-text">Aşağı kaydırdıkça video ilerler ve ölçüm değerleri onunla birlikte değişir. Kesme pasosu boyadan birkaç mikron alır; bu yüzden her adımda yeniden ölçüyoruz.</p>
        </div>

        <dl class="glass grid grid-cols-2 gap-px overflow-hidden lg:col-span-5">
            <div class="bg-white/[0.02] p-5">
                <dt class="text-[11px] uppercase tracking-[0.14em] text-ink-faint">Boya kalınlığı</dt>
                <dd class="readout mt-2 text-xl text-ink">138 <span class="text-ink-faint">&rarr;</span> 129 <span class="text-sm text-ink-muted">µm</span></dd>
            </div>
            <div class="bg-white/[0.02] p-5">
                <dt class="text-[11px] uppercase tracking-[0.14em] text-ink-faint">Parlaklık</dt>
                <dd class="readout mt-2 text-xl text-ink">41 <span class="text-ink-faint">&rarr;</span> 94 <span class="text-sm text-ink-muted">GU</span></dd>
            </div>
        </dl>
    </div>
</section>

<section class="scrub-track" id="scrubTrack" aria-label="Düzeltme pasosu, scroll ile ilerler">
    <div class="scrub">
        <div class="scrub-stage" id="scrubStage">
            <img src="<?= e(asset('img/paso.webp')) ?>"
                 alt="Pasta makinesiyle kaput üzerinde ilerleyen düzeltme pasosu"
                 width="1280" height="720" loading="lazy" decoding="async">
            <video id="scrubVideo" data-src="<?= e(asset('video/paso.mp4')) ?>"
                   poster="<?= e(asset('img/paso.webp')) ?>"
                   muted playsinline preload="none" aria-hidden="true" tabindex="-1"></video>
            <span class="scrub-dull"></span>
            <span class="scrub-beam"></span>
        </div>

        <aside class="scrub-panel">
            <p class="eyebrow-label">Kesme pasosu, canlı ölçüm</p>

            <div>
                <span class="gauge-value"><span id="gaugeMicron">138</span><span class="gauge-unit">µm</span></span>
                <span class="gauge-label">Boya kalınlığı, kaput orta nokta</span>
            </div>

            <div>
                <span class="gauge-value"><span id="gaugeGloss">41</span><span class="gauge-unit">GU</span></span>
                <span class="gauge-label">Parlaklık ölçer, 60 derece</span>
            </div>

            <div class="flex flex-col gap-2">
                <div class="scrub-progress">
                    <span>Paso ilerlemesi</span>
                    <span class="scrub-progress-val" id="scrubPercent">%0</span>
                </div>
                <div class="gauge-bar" aria-hidden="true"><span class="gauge-fill"></span></div>
            </div>

            <p class="body-sm">Kalan vernik payını ölçmeden ikinci pasoya geçmiyoruz. Güvenli sınırın altına inecekse durumu yazılı bildiriyoruz.</p>
        </aside>
    </div>
</section>
