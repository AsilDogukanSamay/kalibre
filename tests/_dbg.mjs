import { chromium } from 'playwright-core';
const b = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const p = await (await b.newContext({ viewport:{width:1440,height:900} })).newPage();
await p.goto('http://127.0.0.1:5174/', { waitUntil:'networkidle' });
console.log(JSON.stringify(await p.evaluate(() => {
  const tanimli = new Set();
  let hata = null, kuralSayisi = 0;
  const gez = (liste) => {
    for (const kural of liste) {
      kuralSayisi++;
      if (kural.cssRules) { gez(kural.cssRules); continue; }
      if (!kural.selectorText) continue;
      for (const esleme of kural.selectorText.matchAll(/\.((?:[\w-]|\.)+)/g)) {
        tanimli.add(esleme[1].replace(/\(.)/g, '$1'));
      }
    }
  };
  for (const s of document.styleSheets) {
    try { gez(s.cssRules); } catch (e) { hata = e.name; }
  }
  return {
    kuralSayisi, hata, tanimliAdet: tanimli.size,
    ornekler: [...tanimli].slice(0, 12),
    shellVar: tanimli.has('shell'), starVar: tanimli.has('star'),
  };
}, null, 1)));
await b.close();
