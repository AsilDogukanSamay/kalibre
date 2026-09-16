<?php /**
 * Editoryal kural: METIN okunabilir genislikte (.shell) kalir,
 * GORSEL tam ekrana tasar. Hemen altindaki scroll bolumu de tam ekran
 * oldugu icin iki gosterim bolumu ayni dili konusur.
 */ ?>
<section id="calismalar" class="section">

    <div class="shell">
        <div class="section-head" data-reveal>
            <span class="eyebrow-label">Aynı kaput, iki paso</span>
            <h2 class="h-section" data-satir>Önce ve sonra, aynı kadrajdan.</h2>
            <p class="body-text">Sürgüyü kaydırın. Soldaki kare işlem öncesi ölçüm sırasında, sağdaki iki kademeli düzeltme ve seramik uygulamasından sonra çekildi.</p>
        </div>
    </div>

    <div class="compare" data-compare>
        <!-- Sanat yonetimi: her kirilma noktasinin kendi kirpimi var.
             Genis ekranda sinematik bant (2.40:1), dar ekranda daha dikey
             kare (1.50:1). Iki kare de ayni offset'ten kesildi, aksi halde
             surgu hizasi kayardi. Boylece hicbir boyutta gorsel ezilmiyor. -->
        <picture>
            <source media="(min-width: 640px)" srcset="<?= e(asset('img/kaput-oncesi-genis.webp')) ?>" width="1600" height="667">
            <img class="compare-img" src="<?= e(asset('img/kaput-oncesi-dar.webp')) ?>" alt="Düzeltme öncesi, hologram izleriyle dağılmış yansıma" width="1341" height="894" loading="lazy" decoding="async">
        </picture>
        <picture class="compare-after">
            <source media="(min-width: 640px)" srcset="<?= e(asset('img/kaput-sonrasi-genis.webp')) ?>" width="1600" height="667">
            <img class="compare-img" src="<?= e(asset('img/kaput-sonrasi-dar.webp')) ?>" alt="Düzeltme sonrası, keskin ve kesintisiz yansıma" width="1341" height="894" loading="lazy" decoding="async">
        </picture>
        <div class="compare-handle" data-compare-handle aria-hidden="true">
            <span class="compare-grip">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M9 6 4 12l5 6M15 6l5 6-5 6"/>
                </svg>
            </span>
        </div>
        <span class="compare-tag left-4 sm:left-6">İşlem öncesi</span>
        <span class="compare-tag right-4 sm:right-6">İşlem sonrası</span>
        <input class="compare-range" type="range" min="0" max="100" value="50" step="0.5" aria-label="Öncesi ve sonrası karşılaştırma sürgüsü" data-compare-range>
    </div>

    <div class="shell">
        <!-- Bolumun sonucu: iki olculmus deger. Cerceve buraya ait, basliga degil. -->
        <div class="verdict mt-8" data-reveal>
            <span class="verdict-rule" aria-hidden="true"></span>
            <p class="shrink-0">
                <span class="verdict-key">Boya kalınlığı, kaput</span>
                <strong class="verdict-val">138 &rarr; 129 <span class="verdict-birim">&micro;m</span></strong>
            </p>
            <p class="shrink-0 verdict-note">
                <span class="verdict-key">Parlaklık, kaput</span>
                <strong class="verdict-val">41 &rarr; 94 <span class="verdict-birim">GU</span></strong>
            </p>
        </div>
        <p class="note mt-3 max-w-[86ch]">
            Bu bölümdeki kareler ve ölçüm değerleri temsilidir, gerçek bir müşteri aracına ait
            değildir. Yayına alınırken atölyede aynı kadrajdan çekilmiş gerçek karelerle
            değiştirilecek.
        </p>
    </div>

</section>
