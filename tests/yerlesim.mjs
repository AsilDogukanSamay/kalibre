/**
 * Yerlesim denetimi - her sayfa, her kirilma noktasi.
 *
 *   node tests/yerlesim.mjs
 *
 * Gereksinim: playwright-core ve yerel bir Chrome (bkz. tests/kontrast.mjs).
 *
 * Uc seyi olcer, hicbirini varsaymaz:
 *   1. Yatay tasma  - govde hicbir genislikte yana kaymamali (0 px).
 *   2. Baslik yapisi - her sayfada tam bir h1, atlanan seviye yok.
 *   3. Gorsel alt metni - alt niteligi olmayan gorsel yok.
 */
import { chromium } from 'playwright-core';

const TABAN = process.env.BASE_URL ?? 'http://127.0.0.1:5174';
const CHROME = process.env.CHROME ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const YOLLAR = ['/', '/case', '/kvkk', '/gizlilik', '/yonetim/giris'];
const GENISLIKLER = [360, 390, 768, 1024, 1440, 1920];

const denetle = () => {
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
