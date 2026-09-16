<?php
/**
 * Referanslar.
 *
 * Bes yildiz KALDIRILDI. Her sitede ayni sekilde duruyor, hicbir sey olcmuyor
 * ve okuyan kimse ona bakmiyordu. Yerine her referansin altinda o araca ait
 * OLCUM sonucu var: sayfanin geri kalani neyi yapiyorsa - iddiayi rakama
 * baglamak - sosyal kanit da artik onu yapiyor.
 *
 * Kart da degil: sayfada zaten dort ayri kart izgarasi vardi (hizmet, surec,
 * fiyat, sss). Referanslar ustten cizgili editoryal sutun.
 *
 * @var array<int,array<string,mixed>> $testimonials
 */
?>
<section class="section shell">
    <div class="section-head" data-reveal>
        <span class="eyebrow-label">Müşteri geri bildirimi</span>
        <h2 class="h-section text-balance" data-satir>Teslimde rapor veriyoruz, fark oradan başlıyor.</h2>
    </div>

    <div class="grid gap-x-8 gap-y-10 md:grid-cols-3" data-reveal-group>
        <?php foreach ($testimonials as $t): ?>
            <figure class="referans" data-reveal>
                <blockquote class="referans-quote">&ldquo;<?= e($t['quote']) ?>&rdquo;</blockquote>

                <figcaption class="referans-kim">
                    <span class="referans-ad"><?= e($t['name']) ?></span>
                    <span class="referans-arac"><?= e($t['role']) ?></span>
                </figcaption>

                <div class="referans-olcum">
                    <span class="referans-key"><?= e($t['olcum']['key']) ?></span>
                    <span class="referans-val">
                        <?= e($t['olcum']['value']) ?><?php if ($t['olcum']['unit'] !== ''): ?><span class="referans-birim"><?= e($t['olcum']['unit']) ?></span><?php endif; ?>
                    </span>
                </div>
            </figure>
        <?php endforeach; ?>
    </div>

    <!-- Ayni bilgilendirme oncesi/sonrasi bolumunde de var. Sayfa kurgu
         icerigi gizlemiyor; bolumun kendi altinda soyluyor. -->
    <p class="note mt-10" data-reveal>Bu bölümdeki görüşler ve ölçüm değerleri temsilidir. Gerçek teslim raporlarında aynı üç büyüklük &mdash; kalan vernik kalınlığı, parlaklık ve su temas açısı &mdash; panel panel kayıt altına alınır.</p>
</section>
