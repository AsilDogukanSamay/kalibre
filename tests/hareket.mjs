/**
 * Hareket denetimi - scroll'a bagli animasyonlar gercekten hareket ediyor mu?
 *
 * Neden ayri bir denetim: bu animasyonlarda JavaScript yok. Kural dosyada
 * duruyor diye calistigi anlamina gelmiyor - `animation-timeline: view()`
 * elemanin gorunurlugunu EN YAKIN KAYDIRMA KABINA gore olcer ve arada
 * `overflow: hidden` olan bir kutu varsa ilerleme sabit %50'de DONAR.
 * Tam olarak bu oldu; CSS dogru gorunuyordu ama hicbir sey kimildamiyordu.
 * Metin denetimi bunu yakalayamaz, yalnizca olcum yakalar.
 *
 * Ayrica: tarayici sekmesi gizliyken (document.hidden) scroll animasyonlari
 * askiya alinir ve her deger `none` okunur. Bu denetim gorunur bir sayfada
 * calisir; ilk olcum gizli bir panelde alindigi icin "calismiyor" sanilmisti.
 *
 *   node tests/hareket.mjs
 *   CHROME=/yol/chrome.exe node tests/hareket.mjs
 */
import { chromium } from 'playwright-core';
import { chromeYolu } from './chrome-yolu.mjs';

const TABAN  = process.env.BASE_URL ?? 'http://127.0.0.1:5174';
const CHROME = chromeYolu();

let gecti = 0;
let kaldi = 0;

function kontrol(ad, ok, ayrinti = '') {
  if (ok) { gecti++; console.log(`  [ok]   ${ad}`); }
  else    { kaldi++; console.log(`  [FAIL] ${ad} ${ayrinti}`); }
}

/** Sayfayi bastan sona gezip her duragakta olculen degerleri toplar. */
async function olc(sayfa) {
  return sayfa.evaluate(async () => {
    document.documentElement.style.scrollBehavior = 'auto';
    const bekle = (ms) => new Promise((r) => setTimeout(r, ms));

    const hedefler = {
      kart: document.querySelector('#hizmetler .cell-photo'),
      adim: document.querySelector('#surec .flow-photo'),
      hero: document.querySelector('.hero .hero-media'),
    };

    const toplam = document.documentElement.scrollHeight - window.innerHeight;
    const kayit  = { kart: [], adim: [], hero: [] };

    for (let i = 0; i <= 12; i++) {
      window.scrollTo({ top: Math.round((toplam * i) / 12), behavior: 'instant' });
      await bekle(140);
      kayit.kart.push(getComputedStyle(hedefler.kart).translate);
      kayit.adim.push(getComputedStyle(hedefler.adim).translate);
      kayit.hero.push(getComputedStyle(hedefler.hero).scale);
    }

    return { gizli: document.hidden, kayit };
  });
}

/** "0px -7%" -> -7 ; "1.04" -> 1.04 ; "none" -> null */
const sayiya = (deger) => {
  if (!deger || deger === 'none') return null;
  const m = String(deger).match(/-?\d+(\.\d+)?(?=%?\s*$)/);
  return m ? Number(m[0]) : null;
};

const yayilim = (dizi) => {
  const sayilar = dizi.map(sayiya).filter((n) => n !== null);
  if (sayilar.length === 0) return 0;
  return Math.max(...sayilar) - Math.min(...sayilar);
};

const tarayici = await chromium.launch({ executablePath: CHROME });

try {
  // --- 1. Normal tercih: hareket olmali ---------------------------------
  console.log('\nScroll\'a bagli hareket');
  const sayfa = await tarayici.newPage({ viewport: { width: 1440, height: 900 } });
  await sayfa.goto(TABAN, { waitUntil: 'networkidle' });

  const { gizli, kayit } = await olc(sayfa);
  kontrol('sayfa gorunur durumda olculdu', gizli === false,
    'document.hidden true ise tarayici scroll animasyonlarini askiya alir');

  // Anahtar kareler -%7 ile +%7 arasi: en az 10 puanlik yayilim bekleriz.
  const kartY = yayilim(kayit.kart);
  const adimY = yayilim(kayit.adim);
  kontrol('hizmet fotografi scroll ile kayiyor', kartY >= 10, `yayilim: ${kartY.toFixed(2)} puan`);
  kontrol('surec fotografi scroll ile kayiyor', adimY >= 10, `yayilim: ${adimY.toFixed(2)} puan`);

  // Hero olcegi 1.04 -> 1.14, yani 0.10 birim.
  const heroY = yayilim(kayit.hero);
  kontrol('hero videosu scroll ile yaklasiyor', heroY >= 0.05, `yayilim: ${heroY.toFixed(3)} birim`);

  /*
   * Paralaks `translate`, hover buyutmesi `transform` kullanir. Ikisi ayri
   * ozellik oldugu icin cakismazlar; ayni ozellikte olsalardi animasyon
   * hover'i ezerdi. Ikisinin de canli DOM'da ayakta oldugunu dogruluyoruz.
   */
  const birlikte = await sayfa.evaluate(() => {
    const foto = document.querySelector('#hizmetler .cell-photo');
    const cs = getComputedStyle(foto);
    return { translate: cs.translate, transformTanimli: cs.transitionProperty.includes('transform') };
  });
  kontrol('paralaks hover buyutmesini ezmiyor',
    birlikte.translate !== 'none' && birlikte.transformTanimli,
    JSON.stringify(birlikte));

  await sayfa.close();

  // --- 2. Hareket azaltma tercihi: hareket OLMAMALI ---------------------
  console.log('\nHareket azaltma tercihi');
  const sakin = await tarayici.newPage({
    viewport: { width: 1440, height: 900 },
    reducedMotion: 'reduce',
  });
  await sakin.goto(TABAN, { waitUntil: 'networkidle' });

  const sakinOlcum = await olc(sakin);
  kontrol('hizmet fotografi sabit kaliyor', yayilim(sakinOlcum.kayit.kart) === 0);
  kontrol('surec fotografi sabit kaliyor',  yayilim(sakinOlcum.kayit.adim) === 0);
  kontrol('hero videosu sabit kaliyor',     yayilim(sakinOlcum.kayit.hero) === 0);
  await sakin.close();
} finally {
  await tarayici.close();
}

console.log(`\n${gecti} gecti, ${kaldi} kaldi\n`);
process.exit(kaldi === 0 ? 0 : 1);
