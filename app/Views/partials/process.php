<?php
/**
 * Sureç şeridi.
 *
 * Kartlar yan yana duruyordu ama aralarinda bir iliski gorunmuyordu; oysa bu
 * bir sira. Adimlar bir seride diziliyor ve seridi dolduran cizgi bolum
 * gorunurken ilerliyor. Ilerleme degeri inline stille degil, sayfaya bir kez
 * eklenen tek kurallik stylesheet uzerinden tasinan CSS degiskeniyle
 * guncelleniyor (surgu ve scroll bolumuyle ayni yontem).
 *
 * @var array<int,array<string,string>> $process
 * @var array<string,string> $processSummary
 */
?>
<section id="surec" class="section shell" data-flow>
    <div class="section-head">
        <span class="eyebrow-label">Süreç</span>
        <h2 class="h-section text-balance">Araç girdiğinde ne oluyor?</h2>
        <p class="body-text">Dört aşama. Her aşamanın bir çıktısı var; işlem sonunda hepsi tek dosya halinde size veriliyor.</p>
    </div>

    <ol class="flow" data-reveal-group>
        <?php foreach ($process as $i => $step): ?>
            <?php
                $sira    = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                $gorsel  = !empty($step['image']) && asset_exists('img/' . $step['image']);
                $sonAdim = $i === count($process) - 1;
            ?>
            <li class="flow-step" data-reveal>

                <div class="flow-rail" aria-hidden="true">
                    <span class="flow-dot"><span class="flow-dot-core"></span></span>
                    <?php if (!$sonAdim): ?>
                        <span class="flow-line"><span class="flow-line-fill"></span></span>
                    <?php endif; ?>
                </div>

                <article class="flow-card">
                    <?php if ($gorsel): ?>
                        <div class="flow-media">
                            <img class="flow-photo" src="<?= e(asset('img/' . $step['image'])) ?>" alt=""
                                 width="1200" height="492" loading="lazy" decoding="async">
                            <span class="flow-media-scrim"></span>
                        </div>
                    <?php endif; ?>

                    <div class="flow-body">
                        <div class="flow-head">
                            <span class="flow-no-flat"><?= e($sira) ?></span>
                            <h3 class="flow-title"><?= e($step['title']) ?></h3>
                        </div>
                        <p class="body-sm"><?= e($step['body']) ?></p>

                        <dl class="flow-meta">
                            <div>
                                <dt class="flow-meta-key">Süre</dt>
                                <dd class="flow-meta-val"><?= e($step['time']) ?></dd>
                            </div>
                            <div>
                                <dt class="flow-meta-key">Elinize geçen</dt>
                                <dd class="flow-meta-out"><?= e($step['output']) ?></dd>
                            </div>
                        </dl>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ol>

    <p class="flow-total" data-reveal>
        <span class="flow-total-key">Araç atölyede toplam</span>
        <strong class="flow-total-val"><?= e($processSummary['total']) ?></strong>
        <span class="note flow-total-note"><?= e($processSummary['note']) ?></span>
    </p>
</section>
