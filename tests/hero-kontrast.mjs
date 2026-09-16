/**
 * Hero kontrasti - piksel yontemi.
 *
 *   node tests/hero-kontrast.mjs
 *
 * NEDEN AYRI BIR BETIK
 * tests/kontrast.mjs zemini CSS katmanlarindan hesaplar. Hero'da bu yontem
 * gecersizdir, iki sebeple:
 *   - Panelin arkasinda hareketli bir video vardir, zemin kareden kareye degisir.
 *   - Perde (scrim) bir ata elemanin arka plani degil, ayri bir ortu katmanidir;
 *     ata zincirini yurumek onu hic gormez. Perde cevabin parcasidir.
 *
 * YONTEM
 * Tek durust olcum, yaziyi gizleyip tam olarak kapladigi dikdortgeni
 * fotograflamak ve arkada gercekte ne oldugunu olcmektir:
 *   1. Video N zaman damgasina sarilir.
 *   2. Hero icindeki tum yazilar gorunmez yapilir (kutular yerinde kalir).
 *   3. Hero bir kez fotograflanir; kare video + perde + cam + gradyan,
 *      yani tarayicinin gercekten cizdigi her sey.
 *   4. Fotograf tarayiciya geri verilip her yazinin kutusundaki ortalama
 *      renk okunur ve metin rengiyle kontrast hesaplanir.
 *
 * "En kotu kare" boylece bir tahmin degil, videonun kendisinden gelir.
 */
import { chromium } from 'playwright-core';

const TABAN = process.env.BASE_URL ?? 'http://127.0.0.1:5174';
const CHROME = process.env.CHROME ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const KARE_SAYISI = Number(process.env.KARE ?? 10);
const GENISLIKLER = (process.env.GENISLIK ?? '390,1440,1920').split(',').map(Number);

/* ---------------------------------------------------- tarayici ici yardimcilar */

const HAZIRLA = async () => {
  const ayristir = (renk) => {
    const s = renk.match(/[\d.]+/g).map(Number);
    return { r: s[0], g: s[1], b: s[2], a: s.length > 3 ? s[3] : 1 };
  };

  const kok = document.querySelector('.hero-inner');
  const hedefler = [];

  for (const el of kok.querySelectorAll('*')) {
    const yazi = [...el.childNodes].filter((n) => n.nodeType === 3)
      .map((n) => n.textContent.trim()).join(' ').trim();
    if (yazi === '') continue;

    const s = getComputedStyle(el);
    if (s.visibility === 'hidden' || s.display === 'none') continue;

    const kutu = el.getBoundingClientRect();
    if (kutu.width < 2 || kutu.height < 2) continue;
    if (kutu.bottom < 0 || kutu.top > innerHeight) continue;

    const punto = parseFloat(s.fontSize);
    const kalin = parseInt(s.fontWeight, 10) >= 700;

    el.dataset.olcum = String(hedefler.length);
    hedefler.push({
      metin: yazi.slice(0, 46),
      renk: ayristir(s.color),
      kutu: { x: kutu.x, y: kutu.y, w: kutu.width, h: kutu.height },
      punto,
      esik: punto >= 24 || (punto >= 18.66 && kalin) ? 3 : 4.5,
      enKotu: Infinity,
      enKotuAn: 0,
    });
  }

  const heroKutu = kok.getBoundingClientRect();
  window.__olcum = {
    hedefler,
    hero: { x: heroKutu.x, y: heroKutu.y, w: heroKutu.width, h: heroKutu.height },
    sure: 0,
  };

  // Video 640px altinda bilincli olarak hic indirilmez; o durumda zemin
  // poster gorselidir ve tek kare olcmek yeterlidir.
  const video = document.getElementById('heroVideo');
  if (!video) return { adet: hedefler.length, hata: 'hero videosu bulunamadi' };

  if (!video.src && video.dataset.src) video.src = video.dataset.src;
  video.muted = true;
  await new Promise((r) => {
    if (video.readyState >= 2) return r();
    video.addEventListener('loadeddata', r, { once: true });
    setTimeout(r, 10000);
  });
  if (!video.videoWidth) return { adet: hedefler.length, hata: 'video indirilmedi' };

  window.__olcum.sure = video.duration || 20;
  return { adet: hedefler.length, sure: window.__olcum.sure };
};

const SAR = async (an) => {
  const video = document.getElementById('heroVideo');
  video.currentTime = an;
  await new Promise((r) => {
    video.addEventListener('seeked', r, { once: true });
    setTimeout(r, 2000);
  });
  // Yazilar gizlenir; kutular ve arkalarindaki her katman yerinde kalir.
  document.querySelectorAll('[data-olcum]').forEach((el) => { el.style.color = 'transparent'; });
  await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
};

const OKU = async ([b64, an]) => {
  const { hedefler, hero } = window.__olcum;

  const img = new Image();
  img.src = 'data:image/png;base64,' + b64;
  await img.decode();

  const tuval = document.createElement('canvas');
  tuval.width = img.width;
  tuval.height = img.height;
  const ctx = tuval.getContext('2d', { willReadFrequently: true });
  ctx.drawImage(img, 0, 0);

  const parlaklik = ({ r, g, b }) => {
    const [R, G, B] = [r, g, b].map((v) => {
      const c = v / 255;
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * R + 0.7152 * G + 0.0722 * B;
  };
  const oran = (x, y) => {
    const [a, b] = [parlaklik(x), parlaklik(y)].sort((m, n) => n - m);
    return (a + 0.05) / (b + 0.05);
  };

  for (const h of hedefler) {
    const x = Math.max(0, Math.round(h.kutu.x - hero.x));
    const y = Math.max(0, Math.round(h.kutu.y - hero.y));
    const w = Math.max(1, Math.min(tuval.width - x, Math.round(h.kutu.w)));
    const p = Math.max(1, Math.min(tuval.height - y, Math.round(h.kutu.h)));
    if (x >= tuval.width || y >= tuval.height) continue;

    const veri = ctx.getImageData(x, y, w, p).data;
    let r = 0, g = 0, b = 0;
    const n = veri.length / 4;
    for (let k = 0; k < veri.length; k += 4) { r += veri[k]; g += veri[k + 1]; b += veri[k + 2]; }

    const d = oran(h.renk, { r: r / n, g: g / n, b: b / n });
    if (d < h.enKotu) { h.enKotu = d; h.enKotuAn = an; }
  }

  // Yazilar geri gelir (sonraki kare icin sayfa normale doner).
  document.querySelectorAll('[data-olcum]').forEach((el) => { el.style.color = ''; });
};

const SONUC = () => window.__olcum.hedefler.map(
  ({ metin, punto, esik, enKotu, enKotuAn }) => ({ metin, punto, esik, enKotu, enKotuAn })
);

/* ---------------------------------------------------------------- calistir */

const tarayici = await chromium.launch({ executablePath: CHROME, headless: true });
let altta = 0;

for (const genislik of GENISLIKLER) {
  const ctx = await tarayici.newContext({
    viewport: { width: genislik, height: 900 },
    deviceScaleFactor: 1,
  });
  const sayfa = await ctx.newPage();
  await sayfa.goto(TABAN + '/', { waitUntil: 'networkidle' });

  const hazir = await sayfa.evaluate(HAZIRLA);
  if (hazir.hata) {
    // 640px altinda video bilincli olarak hic indirilmez; zemin poster gorselidir.
    console.log(`\n${genislik}px · ${hazir.hata} (dar ekranda video indirilmiyor, poster olculur)`);
  }

  const hero = await sayfa.evaluate(() => {
    const k = document.querySelector('.hero-inner').getBoundingClientRect();
    return { x: k.x, y: k.y, width: k.width, height: Math.min(k.height, innerHeight - k.y) };
  });

  const sure = hazir.sure ?? 1;
  for (let i = 0; i < KARE_SAYISI; i++) {
    const an = (sure * i) / KARE_SAYISI;
    if (!hazir.hata) await sayfa.evaluate(SAR, an);
    else await sayfa.evaluate(() => {
      document.querySelectorAll('[data-olcum]').forEach((el) => { el.style.color = 'transparent'; });
    });

    const png = await sayfa.screenshot({ clip: hero, scale: 'css' });
    await sayfa.evaluate(OKU, [png.toString('base64'), an]);

    if (hazir.hata) break; // video yoksa tek kare yeter
  }

  const sonuc = await sayfa.evaluate(SONUC);
  console.log(`\n${genislik}px · ${hazir.hata ? 'poster' : KARE_SAYISI + ' kare'} tarandi`);

  for (const h of sonuc.sort((a, b) => a.enKotu - b.enKotu)) {
    const gecti = h.enKotu >= h.esik;
    if (!gecti) altta++;
    console.log(
      `  ${h.enKotu.toFixed(2).padStart(6)}:1  esik ${h.esik}  ${String(h.punto).padStart(6)}px  ` +
      `${gecti ? 'gecti' : 'KALDI'}  "${h.metin}"`
    );
  }

  await ctx.close();
}

await tarayici.close();
console.log(`\nEsik altinda kalan: ${altta}`);
process.exit(altta === 0 ? 0 : 1);
