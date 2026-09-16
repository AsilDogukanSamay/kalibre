<?php
/**
 * Editoryal kesinti.
 *
 * Sayfanin geri kalani tek bir iskelette akiyor: etiket -> iki satir baslik ->
 * paragraf -> kart izgarasi. Alti bolum ust uste ayni kaliba oturunca sayfa tek
 * nefeste okunuyordu. Bu bolum o kaliba UYMUYOR ve bu bilincli: etiket yok,
 * kart yok, izgara yok.
 *
 * Sagdaki kesit FOTOGRAF DEGIL, bilincli olarak. Buraya bir atolye karesi
 * koymak bolumu diger alti bolumden ayirt edilemez hale getirirdi. Onun yerine
 * cumlenin KANITI duruyor: "kalan vernik karar verir" deniyor ama neyin ne
 * kadar kaldigi sayfanin hicbir yerinde gorulmuyordu.
 *
 * Katman yukseklikleri uydurma degil: flex-grow degerleri mikron degerlerinin
 * kendisi (48 / 22 / 38 / 30), yani cizim olcekli. Toplam 138, hero'daki
 * olcumle ayni rakam.
 */
?>
<section class="manifesto">
    <div class="shell">
        <div class="manifesto-izgara">

            <div class="manifesto-in" data-reveal>
                <p class="manifesto-quote" data-satir>Nerede duracağımıza cila değil, kalan vernik karar verir.</p>

                <div class="manifesto-note">
                    <p class="body-text">Her paso öncesi ve sonrası ölçüyoruz. Rakam güvenli sınırın altına inecekse ikinci pasoya geçmiyor, durumu yazılı olarak bildiriyoruz.</p>
                    <p class="body-text">Bir aracı olduğundan iyi göstermek bir günlük iştir. Ölçüm raporu ise araçla birlikte kalır.</p>
                </div>
            </div>

            <figure class="kesit" data-reveal>
                <figcaption class="kesit-ust">
                    <span class="kesit-key">Kaput kesiti</span>
                    <span class="kesit-toplam"><span class="readout">138</span><span class="kesit-birim">&micro;m toplam film</span></span>
                </figcaption>

                <div class="kesit-govde">
                    <div class="kesit-kat kesit-vernik">
                        <span class="kesit-ad">Vernik</span>
                        <span class="kesit-deger">48<span class="kesit-birim-ic">&micro;m</span></span>

                        <!-- Guvenli sinir vernik katmaninin ICINDE duruyor: cizginin
                             ustunde kalan ~18 mikron calisma payi, altinda kalan
                             30 mikron dokunulmaz. Konum yuzdesi 30/48 oraninin
                             kendisi, yani cizgi de olcekli. -->
                        <span class="kesit-sinir">
                            <span class="kesit-sinir-et">güvenli sınır &middot; 30 &micro;m</span>
                        </span>
                    </div>

                    <div class="kesit-kat kesit-boya">
                        <span class="kesit-ad">Renk katı</span>
                        <span class="kesit-deger">22<span class="kesit-birim-ic">&micro;m</span></span>
                    </div>

                    <div class="kesit-kat kesit-astar">
                        <span class="kesit-ad">Astar</span>
                        <span class="kesit-deger">38<span class="kesit-birim-ic">&micro;m</span></span>
                    </div>

                    <div class="kesit-kat kesit-ekaplama">
                        <span class="kesit-ad">Elektro kaplama</span>
                        <span class="kesit-deger">30<span class="kesit-birim-ic">&micro;m</span></span>
                    </div>

                    <div class="kesit-sac"><span class="kesit-ad">Galvanizli sac</span></div>
                </div>

                <p class="kesit-not">Kırmızı bant kesme pasosunun alabileceği yer: güvenli sınırın üstünde kalan <strong class="kesit-vurgu">yaklaşık 18 &micro;m</strong>, paso başına 2&ndash;4 &micro;m. Altındaki 30 &micro;m&rsquo;a dokunulmuyor. Ölçüm cihazı toplam film kalınlığını verir; değerler tipik bir kaput içindir ve her araçta yeniden ölçülür.</p>
            </figure>

        </div>
    </div>
</section>
