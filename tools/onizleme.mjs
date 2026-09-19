/**
 * Statik onizleme ureticisi  —  npm run onizleme
 *
 * NE YAPAR
 * Calisan yerel sunucudan dort genel sayfayi cekip statik HTML olarak
 * yazar, varliklari kopyalar ve GitHub Pages'te yasayabilecek hale getirir.
 * Cikti: onizleme/  (gh-pages dalina gonderilir)
 *
 * NEDEN GEREKLI
 * GitHub Pages PHP calistirmaz. Uygulamanin kendisi orada yasayamaz ama
 * ON YUZUN neredeyse tamami statik dosyalarla calisir: tasarim, hero ve
 * scroll videolari (Pages bayt araligi destekler), scroll paralaksi,
 * oncesi/sonrasi surgusu, sayaclar, SSS akordeonu, nisangah, geri sayim.
 * Calismayan tek sey iletisim formu — arkasinda PHP yok.
 *
 * FORM SESSIZCE KIRILMAZ
 * Formun calismadigini gizlemek, "formu bozuk" diye okunmasina yol acar.
 * Onizlemede form gorunur bicimde devre disi birakilir ve tam yaninda ne
 * oldugunu soyleyen bir not durur. Calisan surum depoda.
 *
 * NEDEN noindex
 * Sayfa gercek bir markanin kurumsal kimligini tasiyor. Portfolyo baglaminda
 * bu bir vaka calismasi; arama sonuclarinda bir isletme gibi gorunmemeli.
 *
 * KULLANIM
 *   npm run start        (ayri terminalde, sunucu acik olmali)
 *   npm run onizleme
 *   ONIZLEME_KOK=/baska-dizin npm run onizleme     (alt dizin degisirse)
 */
import { mkdir, writeFile, readFile, cp, rm } from 'node:fs/promises';
import path from 'node:path';
import { execSync } from 'node:child_process';

const TABAN = process.env.BASE_URL ?? 'http://127.0.0.1:5174';

/**
 * GitHub Pages proje sayfasi bir ALT DIZINDE yasar ve o dizinin adi DEPO
 * ADIDIR. Onceki halde "/kalibre-landing" sabit yaziliydi: depo yeniden
 * adlandirildiginde butun yollar sessizce 404 donerdi. Artik uzak adresten
 * okunuyor, yani ad degisince kendiliginden uyar.
 */
function depoBilgisi() {
  try {
    const uzak = execSync('git remote get-url origin', { encoding: 'utf8' }).trim();
    const m = uzak.match(/[:/]([^/]+)\/([^/]+?)(?:\.git)?$/);
    if (m) return { sahip: m[1], depo: m[2] };
  } catch { /* uzak tanimli degilse asagidaki varsayilana dusulur */ }
  return { sahip: 'asildogukansamay', depo: 'kalibre-landing' };
}

/**
 * ONIZLEME_KOK degerini normallestirir.
 *
 * Git Bash, "/" ile baslayan ortam degiskenlerini Windows yoluna cevirir:
 * ONIZLEME_KOK=/kalibre komut satirinda "C:/Program Files/Git/kalibre"
 * olarak geliyordu. Windows mutlak yolu geldiyse yalnizca son parca alinir.
 */
function kokuNormallestir(deger) {
  let k = deger.trim().replace(/\\/g, '/');
  if (/^[A-Za-z]:/.test(k)) k = k.split('/').pop();
  return '/' + k.replace(/^\/+|\/+$/g, '');
}

const { sahip, depo } = depoBilgisi();
const KOK = kokuNormallestir(process.env.ONIZLEME_KOK ?? depo);
// github.io alan adlari kucuk harftir; uzak adres buyuk harfli olabilir.
const GENEL = process.env.ONIZLEME_URL ?? `https://${sahip.toLowerCase()}.github.io${KOK}`;
const CIKTI = 'onizleme';

const SAYFALAR = [
  { yol: '/',         dosya: 'index.html' },
  { yol: '/case',     dosya: 'case/index.html' },
  { yol: '/kvkk',     dosya: 'kvkk/index.html' },
  { yol: '/gizlilik', dosya: 'gizlilik/index.html' },
];

/** Mutlak yollari alt dizine tasir: href="/case" -> href="/kalibre-landing/case" */
function yollariTasi(html) {
  return html
    /* Adres tasiyan her nitelik. Onceki desen yalnizca href/src/action/srcset
       tanıyordu ve <video poster="..."> gozden kacti: statik onizlemede
       paso.webp 404 donuyordu. Basina ek alan nitelikler de kapsanir
       (data-src, data-poster gibi). "//" ile baslayanlara dokunulmaz. */
    .replace(/([a-zA-Z-]*(?:href|src|srcset|action|poster))="\/(?!\/)/g, `$1="${KOK}/`)
    // canonical, og:url ve JSON-LD icindeki yerel adres
    .replace(new RegExp(TABAN.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), GENEL);
}

/** noindex + onizleme betigi enjekte eder. Sayfanin tasarimina dokunmaz. */
function isaretleriEkle(html) {
  const bas = '<meta name="robots" content="noindex, nofollow">';
  const son = `<script src="${KOK}/onizleme.js" defer></script>`;
  return html
    .replace('</head>', `    ${bas}\n</head>`)
    .replace('</body>', `${son}\n</body>`);
}

/* Enjekte edilen betik. Ayri dosyada, cunku satir ici betik hem sayfanin
   kendi kurallarina aykiri hem de okunmasi zor. */
const ONIZLEME_JS = `/**
 * Statik onizleme katmani. Uygulamanin kendi JavaScript'ine dokunmaz;
 * yalnizca onizlemede gecerli olan iki seyi yapar.
 */
(function () {
  'use strict';

  // 1. Form devre disi. Arkasinda PHP yok; sessizce bosa calismasindansa
  //    acikca kapali olmasi dogru.
  var form = document.querySelector('#iletisim form');
  if (form) {
    form.querySelectorAll('input, textarea, button, select').forEach(function (el) {
      el.disabled = true;
    });

    var not = document.createElement('p');
    not.className = 'note-box max-w-[62ch]';
    not.textContent =
      'Bu statik bir onizlemedir: form burada kayit olusturmaz. ' +
      'Calisan surumde gonderim PHP katmaninda dogrulanip MySQL\\u2019e yaziliyor ' +
      've sonuc bu tasarima uygun bir bildirimle gosteriliyor.';
    form.prepend(not);
  }

  // 2. Alt bilgideki bilgilendirmeye tek cumle eklenir.
  var bilgi = document.querySelector('.note-box');
  if (bilgi && bilgi !== document.querySelector('#iletisim .note-box')) {
    var ek = document.createElement('span');
    ek.textContent = ' Bu adres statik bir onizlemedir; calisan surum kaynak deposundadir.';
    bilgi.appendChild(ek);
  }
})();
`;

/* ---------------------------------------------------------------- calistir */

await rm(CIKTI, { recursive: true, force: true });
await mkdir(CIKTI, { recursive: true });

// Varliklar oldugu gibi kopyalanir: CSS, JS, gorseller, videolar.
await cp('public/assets', path.join(CIKTI, 'assets'), { recursive: true });
console.log('  varliklar kopyalandi');

for (const { yol, dosya } of SAYFALAR) {
  const yanit = await fetch(TABAN + yol);
  if (!yanit.ok) throw new Error(`${yol} -> HTTP ${yanit.status}. Sunucu acik mi? (npm run start)`);

  const html = isaretleriEkle(yollariTasi(await yanit.text()));
  const hedef = path.join(CIKTI, dosya);
  await mkdir(path.dirname(hedef), { recursive: true });
  await writeFile(hedef, html, 'utf8');
  console.log(`  ${yol.padEnd(10)} -> ${dosya}`);
}

/* Cikti denetimi: onekssiz kalan mutlak yol varsa uretim BASARISIZ olur.
   Yeni bir nitelik turu eklendiginde sessizce 404 donmesindense burada
   kalmasi dogru.

   Desen ADRES TASIYAN niteliklerle sinirli: ilk halde her nitelige bakiyordu
   ve CSP nonce'unu yakaladi (base64 degeri "/" ile baslayabiliyor). Ayrica
   varlik yollari nitelikten bagimsiz olarak da taranir - asil ariza bicimi
   odur. */
const kacanlar = [];
for (const { dosya } of SAYFALAR) {
  const icerik = await readFile(path.join(CIKTI, dosya), 'utf8');
  const onek = KOK.slice(1);

  for (const [, nitelik] of icerik.matchAll(
    new RegExp(`([a-zA-Z-]*(?:href|src|srcset|action|poster))="/(?!/)(?!${onek})[^"]*"`, 'g'),
  )) {
    kacanlar.push(`${dosya}: ${nitelik}`);
  }

  for (const [tam] of icerik.matchAll(/"\/assets\/[^"]*"/g)) {
    kacanlar.push(`${dosya}: ${tam}`);
  }
}
if (kacanlar.length) {
  console.error('\nOnekssiz mutlak yol kaldi (GitHub Pages alt dizininde 404 doner):');
  for (const k of [...new Set(kacanlar)]) console.error('  ' + k);
  process.exitCode = 1;
}

await writeFile(path.join(CIKTI, 'onizleme.js'), ONIZLEME_JS, 'utf8');
// Jekyll islemesin: alt cizgiyle baslayan klasorler aksi halde atlanir.
await writeFile(path.join(CIKTI, '.nojekyll'), '', 'utf8');

console.log(`\nHazir: ${CIKTI}/  ->  ${GENEL}`);
console.log('Yayinlamak icin: npm run onizleme:yayinla');
