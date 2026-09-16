/**
 * Yerlesim denetimi - her sayfa, her kirilma noktasi.
 *
 *   node tests/yerlesim.mjs
 *
 * Gereksinim: playwright-core ve yerel bir Chrome (bkz. tests/kontrast.mjs).
 *
 * Dort seyi olcer, hicbirini varsaymaz:
 *   1. Yatay tasma  - govde hicbir genislikte yana kaymamali (0 px).
 *   2. Baslik yapisi - her sayfada tam bir h1, atlanan seviye yok.
 *   3. Gorsel alt metni - alt niteligi olmayan gorsel yok.
 *   4. Tanimsiz sinif - isaretlemede kullanilip hicbir stylesheet'te karsiligi
 *      olmayan sinif. Boyle bir sinif sessizce hicbir sey yapmaz: .star tam
 *      olarak boyleydi, SVG ne boyut ne dolgu aliyordu ve tarayici
 *      varsayilanina duserek siyah ve kocaman ciziyordu.
 */
import { chromium } from 'playwright-core';

const TABAN = process.env.BASE_URL ?? 'http://127.0.0.1:5174';
const CHROME = process.env.CHROME ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const YOLLAR = ['/', '/case', '/kvkk', '/gizlilik', '/yonetim/giris'];
const GENISLIKLER = [360, 390, 768, 1024, 1440, 1920];

const denetle = () => {
  /* ---------------------------------------------------------------
   * Stylesheet'lerde tanimli TUM sinif adlarini topla.
   *
   * Iki tuzak var, ikisi de bu denetim yazilirken yasandi:
   *
   * 1. CSSRuleList yinelenebilir (iterable) DEGIL. CSSOM'da iterable<> olarak
   *    tanimlanmamis, yalnizca indeksli erisimi var; for...of TypeError atiyor.
   *    Array.from ile diziye cevriliyor.
   *
   * 2. "cssRules varsa kapsayicidir" varsayimi YANLIS. Chrome, CSS ic ice
   *    yazim destegiyle birlikte CSSStyleRule'a da cssRules verdi (bos liste).
   *    Once kapsayiciyi kontrol edip continue eden bir dongu hicbir stil
   *    kuralinin selectorText'ine ulasamiyor ve "her sinif tanimsiz" diyordu.
   *    Ikisi ayri ayri kontrol ediliyor.
   * --------------------------------------------------------------- */
  const tanimli = new Set();

  const gez = (liste) => {
    for (const kural of Array.from(liste)) {
      if (kural.selectorText) {
        for (const esleme of kural.selectorText.matchAll(/\.((?:[\w-]|\\.)+)/g)) {
          // Tailwind kacisli secici uretir: .max-w-\[62ch\] -> max-w-[62ch]
          tanimli.add(esleme[1].replace(/\\(.)/g, '$1'));
        }
      }
      if (kural.cssRules && kural.cssRules.length) {
        gez(kural.cssRules); // @media, @supports, ic ice kurallar
      }
    }
  };

  for (const sayfa of Array.from(document.styleSheets)) {
    // Farkli kaynaktan gelen sayfa (Google Fonts) SecurityError atar; o sayfa
    // zaten sinif tanimlamaz, atlanir.
    try { gez(sayfa.cssRules); } catch { /* farkli kaynak */ }
  }

  const tanimsiz = new Map();
  for (const el of document.querySelectorAll('[class]')) {
    for (const sinif of el.classList) {
      if (!tanimli.has(sinif)) tanimsiz.set(sinif, (tanimsiz.get(sinif) ?? 0) + 1);
    }
  }

  const seviyeler = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')]
    .map((h) => Number(h.tagName[1]));

  let atlama = null;
  for (let i = 1; i < seviyeler.length; i++) {
    if (seviyeler[i] - seviyeler[i - 1] > 1) {
      atlama = `h${seviyeler[i - 1]} -> h${seviyeler[i]}`;
      break;
    }
  }

  return {
    tasma: document.documentElement.scrollWidth - document.documentElement.clientWidth,
    h1: document.querySelectorAll('h1').length,
    atlama,
    altsiz: [...document.querySelectorAll('img')].filter((i) => !i.hasAttribute('alt')).length,
    tanimliSayisi: tanimli.size,
    tanimsiz: [...tanimsiz.entries()].map(([ad, adet]) => `${ad} (${adet})`),
  };
};

const tarayici = await chromium.launch({ executablePath: CHROME, headless: true });
let sorun = 0;

for (const genislik of GENISLIKLER) {
  const ctx = await tarayici.newContext({ viewport: { width: genislik, height: 900 } });
  const sayfa = await ctx.newPage();

  for (const yol of YOLLAR) {
    await sayfa.goto(TABAN + yol, { waitUntil: 'networkidle' });
    // Goruse girme animasyonundaki elemanlar olcumden once acilir.
    await sayfa.evaluate(() => {
      document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-in'));
    });
    const r = await sayfa.evaluate(denetle);

    const kusurlar = [];
    if (r.tasma > 0) kusurlar.push(`yatay tasma ${r.tasma}px`);
    if (r.h1 !== 1) kusurlar.push(`h1 sayisi ${r.h1}`);
    if (r.atlama) kusurlar.push(`baslik atlamasi ${r.atlama}`);
    if (r.altsiz > 0) kusurlar.push(`${r.altsiz} gorselde alt yok`);
    if (r.tanimsiz.length) kusurlar.push(`tanimsiz sinif: ${r.tanimsiz.join(', ')}`);

    /*
     * Toplayici bozulursa her sinif "tanimsiz" gorunur ve denetim sessizce ise
     * yaramaz hale gelir - bu tam olarak yasandi. Toplayicinin kendisi de
     * kontrol ediliyor: bu sayfalarda yuzlerce sinif tanimli olmali.
     */
    if (r.tanimliSayisi < 100) {
      kusurlar.push(`sinif toplayici calismiyor (yalnizca ${r.tanimliSayisi} sinif bulundu)`);
    }

    sorun += kusurlar.length;
    console.log(
      `${String(genislik).padStart(4)}px  ${yol.padEnd(16)} ` +
      (kusurlar.length === 0 ? 'temiz' : kusurlar.join(' · '))
    );
  }

  await ctx.close();
}

await tarayici.close();
console.log(`\nToplam kusur: ${sorun}`);
process.exit(sorun === 0 ? 0 : 1);
