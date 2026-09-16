/**
 * Kontrast denetimi - yeni sayfalar (yasal metinler, panel, onay kutusu).
 *
 *   node tests/kontrast.mjs
 *
 * Gereksinim: playwright-core ve yerel bir Chrome.
 *   npm i -D playwright-core
 *   CHROME=/yol/chrome.exe node tests/kontrast.mjs
 *
 * YONTEM
 * Hero bolumunde yazi hareketli videonun uzerinde durur; orada tek durust
 * olcum, yaziyi gizleyip o dikdortgeni fotograflamak ve gercekte arkada ne
 * oldugunu piksel piksel olcmektir.
 *
 * Bu betigin denetledigi sayfalarda ise metnin arkasinda video ya da fotograf
 * yoktur: yalnizca duz renkler ve uzerlerine binen yari saydam cam yuzeyler
 * vardir. Bu durumda dogru zemin, CSS katmanlarini yukari dogru alfa ile
 * bindirerek hesaplanabilir; sonuc piksel olcumuyle ayni degeri verir ve
 * ekran goruntusune bagli olmadigi icin daha kararlidir.
 *
 * Esik: WCAG 2.1 AA -> normal metin 4.5:1, iri metin (>=24px veya >=18.66px
 * kalin) 3.0:1.
 */
import { chromium } from 'playwright-core';

const TABAN = process.env.BASE_URL ?? 'http://127.0.0.1:5174';
const CHROME = process.env.CHROME ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const SAYFALAR = [
  { yol: '/case', ad: 'Vaka çalışması' },
  { yol: '/kvkk', ad: 'KVKK aydınlatma metni' },
  { yol: '/gizlilik', ad: 'Gizlilik ve çerez politikası' },
  { yol: '/yonetim/giris', ad: 'Panel girişi' },
  // Hero disarida birakilir: orada zemin hareketli videodur, CSS katmanlarindan
  // hesaplanamaz. Hero kontrasti piksel yontemiyle ayrica olculdu (README bolum 6).
  { yol: '/', ad: 'Ana sayfa (hero haric)', atla: '.hero-inner' },
  { yol: '/yonetim', ad: 'Panel · talep listesi', giris: true },
];

// Panel oturum ister. Kimlik bilgisi ortam degiskeninden gelir; betikte yazili degildir.
// Ev kurali: notur gri yazilar AAA esigini tutar. Marka renkli yazi bu esige
// cikamaz - kirmizinin 7:1 verdigi nokta somon tonudur, kurumsal kimlik orada
// biter - bu yuzden marka renkli metin AA esiginde degerlendirilir ve yalnizca
// kisa etiketlerde kullanilir. Paragraf hicbir yerde marka renginde degildir.
const NOTUR_HEDEF = Number(process.env.HEDEF ?? 7);

const PANEL_USER = process.env.PANEL_USER ?? '';
const PANEL_PASS = process.env.PANEL_PASS ?? '';

const olcum = () => {
  const ayristir = (renk) => {
    const s = renk.match(/[\d.]+/g)?.map(Number) ?? [];
    return s.length >= 3 ? { r: s[0], g: s[1], b: s[2], a: s.length > 3 ? s[3] : 1 } : null;
  };

  // Ust katmani alttakinin uzerine bindirir (source-over).
  const bindir = (ust, alt) => ({
    r: ust.r * ust.a + alt.r * (1 - ust.a),
    g: ust.g * ust.a + alt.g * (1 - ust.a),
    b: ust.b * ust.a + alt.b * (1 - ust.a),
    a: 1,
  });

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

  // Elemandan yukari cikarak gercek zemini biriktirir.
  const zemin = (el) => {
    const katmanlar = [];
    for (let n = el; n && n !== document.documentElement.parentNode; n = n.parentElement) {
      const bg = ayristir(getComputedStyle(n).backgroundColor);
      if (bg && bg.a > 0) {
        katmanlar.push(bg);
        if (bg.a === 1) break;
      }
    }
    katmanlar.push({ r: 16, g: 18, b: 20, a: 1 }); // --surface-900, son care
    return katmanlar.reduceRight((alt, ust) => bindir(ust, alt));
  };

  const kok = window.__kok ? document.querySelector(window.__kok) : document.body;
  const atla = window.__atla ?? null;
  const bulgular = [];
  let sayilan = 0;

  for (const el of kok.querySelectorAll('*')) {
    const yazi = [...el.childNodes]
      .filter((n) => n.nodeType === 3)
      .map((n) => n.textContent.trim())
      .join(' ')
      .trim();
    if (yazi === '') continue;

    if (atla && el.closest(atla)) continue;

    const s = getComputedStyle(el);
    if (s.visibility === 'hidden' || s.display === 'none' || Number(s.opacity) < 0.15) continue;

    const kutu = el.getBoundingClientRect();
    if (kutu.width < 2 || kutu.height < 2) continue;

    const renk = ayristir(s.color);
    if (!renk) continue;

    const onPlan = renk.a < 1 ? bindir(renk, zemin(el)) : renk;
    const arka = zemin(el);
    const deger = oran(onPlan, arka);

    const punto = parseFloat(s.fontSize);
    const kalin = parseInt(s.fontWeight, 10) >= 700;
    const iri = punto >= 24 || (punto >= 18.66 && kalin);
    const wcag = iri ? 3 : 4.5;

    // AAA hedefi yalnizca NOTUR YAZI + NOTUR ZEMIN icin gecerlidir.
    // Marka renginin girdigi her yerde WCAG AA esigi kullanilir: beyaz yazi
    // kurumsal kirmizi dolgu uzerinde 4,66:1 verir ve bunu yukseltmenin tek
    // yolu kurumsal rengi degistirmektir.
    const kroma = (c) => Math.max(c.r, c.g, c.b) - Math.min(c.r, c.g, c.b);
    const notur = kroma(onPlan) <= 25 && kroma(arka) <= 25;
    const esik = notur ? Math.max(wcag, window.__hedef) : wcag;

    sayilan++;
    if (deger < esik) {
      bulgular.push({
        metin: yazi.slice(0, 60),
        sinif: el.className?.toString().slice(0, 40) ?? '',
        punto: Math.round(punto * 10) / 10,
        olculen: Math.round(deger * 100) / 100,
        esik,
        notur,
      });
    }
  }

  return { sayilan, bulgular };
};

const tarayici = await chromium.launch({ executablePath: CHROME, headless: true });
let toplamBulgu = 0;

for (const genislik of [390, 1440]) {
  const ctx = await tarayici.newContext({ viewport: { width: genislik, height: 900 } });
  const sayfa = await ctx.newPage();

  for (const { yol, ad, kok, atla, giris } of SAYFALAR) {
    if (giris) {
      if (PANEL_USER === '' || PANEL_PASS === '') {
        console.log(`${genislik}px  ${ad.padEnd(34)} atlandi (PANEL_USER / PANEL_PASS verilmedi)`);
        continue;
      }
      await sayfa.goto(TABAN + '/yonetim/giris', { waitUntil: 'networkidle' });
      await sayfa.fill('#user', PANEL_USER);
      await sayfa.fill('#password', PANEL_PASS);
      await sayfa.click('button[type=submit]');
      await sayfa.waitForLoadState('networkidle');
    }

    await sayfa.goto(TABAN + yol, { waitUntil: 'networkidle' });
    await sayfa.evaluate(([k, a, h]) => {
      window.__kok = k ?? null;
      window.__atla = a ?? null;
      window.__hedef = h;
      // Goruse girme animasyonu: ekran altindaki elemanlar saydam basliyor
      // ve olcumden kacarlardi. Olcum oncesi hepsi acilir.
      document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-in'));
    }, [kok ?? null, atla ?? null, NOTUR_HEDEF]);
    const { sayilan, bulgular } = await sayfa.evaluate(olcum);

    toplamBulgu += bulgular.length;
    const durum = bulgular.length === 0 ? 'temiz' : `${bulgular.length} ESIK ALTI`;
    console.log(`${genislik}px  ${ad.padEnd(34)} ${String(sayilan).padStart(3)} metin  ${durum}`);

    for (const b of bulgular) {
      console.log(
        `         ${b.olculen}:1 (esik ${b.esik}${b.notur ? ' notur' : ' marka'})  ` +
        `${b.punto}px  "${b.metin}"  .${b.sinif}`
      );
    }
  }

  await ctx.close();
}

await tarayici.close();
console.log(`Notur yazi hedefi: ${NOTUR_HEDEF}:1 · marka renkli yazi: WCAG AA`);
console.log(`\nToplam esik alti: ${toplamBulgu}`);
process.exit(toplamBulgu === 0 ? 0 : 1);
