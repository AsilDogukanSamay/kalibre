<?php
/**
 * Atolye seridi - tam genislik, uzerinde HIC YAZI YOK.
 *
 * Fotograf once manifestonun ZEMINI olarak denendi ve olcum geri cevirdi:
 * kesit basligindaki notur etiket 5,08:1'e dusuyordu. WCAG AA esigini
 * geciyordu ama bu projenin notur yazi icin koydugu 7:1 kuralinin altinda
 * (kural 6). Perdeyi kaldirmak fotografi %6 gorunurluge indiriyordu, yani
 * fotograf da kazanmiyordu.
 *
 * Sebep aslinda yerlesimdi: manifestonun iki sutunu (cumle ve kesit) zaten
 * doluydu, fotografa bos alan kalmiyordu. Burada durunca iki sorun birden
 * bitiyor - fotograf tam gucunde gorunuyor ve uzerinde olculecek yazi yok.
 *
 * Kenarlardaki yumusak gecis serit "yapistirilmis dikdortgen" gibi
 * durmasin diye: ust ve alt kenar icinde bulundugu sessiz bandin tonuna
 * karisiyor.
 */
?>
<figure class="atolye-serit">
    <picture>
        <source media="(min-width: 640px)" srcset="<?= e(asset('img/atolye-serit.webp')) ?>" width="1600" height="618">
        <img class="atolye-foto" src="<?= e(asset('img/atolye-serit-dar.webp')) ?>"
             alt="Atölyede inceleme lambası altında duran siyah bir kaput; yüzeyde lamba yansımaları görünüyor."
             width="800" height="309" loading="lazy" decoding="async">
    </picture>
    <span class="atolye-kenar"></span>
</figure>
