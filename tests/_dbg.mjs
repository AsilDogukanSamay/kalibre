import { chromium } from 'playwright-core';

const b = await chromium.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
  headless: true,
});
const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
const p = await ctx.newPage();

const hatalar = [];
p.on('pageerror', (e) => hatalar.push(String(e)));
p.on('console', (m) => { if (m.type() === 'error') hatalar.push(m.text()); });

await p.goto('http://127.0.0.1:5174/', { waitUntil: 'networkidle' });
await p.evaluate(() => document.fonts.ready);

// --- Sayac ---
await p.evaluate(() => {
  const y = document.querySelector('[data-sayac]').getBoundingClientRect().top + scrollY;
  scrollTo({ top: y - 400, behavior: 'instant' });
});
await p.waitForTimeout(250);
const yolda = await p.evaluate(() => [...document.querySelectorAll('[data-sayac]')].map((e) => e.textContent));
await p.waitForTimeout(1600);
const bitti = await p.evaluate(() => [...document.querySelectorAll('[data-sayac]')].map((e) => e.textContent));

// --- SSS ---
await p.evaluate(() => {
  const y = document.getElementById('sss').getBoundingClientRect().top + scrollY;
  scrollTo({ top: y - 80, behavior: 'instant' });
});
await p.waitForTimeout(400);
const once = await p.evaluate(() => {
  const d = document.querySelector('[data-faq="0"]');
  return { acik: d.open, panel: Math.round(d.querySelector('.faq-panel').getBoundingClientRect().height) };
});
await p.click('[data-faq="0"] .faq-summary');
await p.waitForTimeout(150);
const ortada = await p.evaluate(() => {
  const d = document.querySelector('[data-faq="0"]');
  return { acik: d.open, panel: Math.round(d.querySelector('.faq-panel').getBoundingClientRect().height) };
});
await p.waitForTimeout(500);
const sonra = await p.evaluate(() => {
  const d = document.querySelector('[data-faq="0"]');
  return { acik: d.open, panel: Math.round(d.querySelector('.faq-panel').getBoundingClientRect().height) };
});
await p.click('[data-faq="0"] .faq-summary');
await p.waitForTimeout(700);
const kapandi = await p.evaluate(() => {
  const d = document.querySelector('[data-faq="0"]');
  return { acik: d.open, panel: Math.round(d.querySelector('.faq-panel').getBoundingClientRect().height) };
});

const inline = await p.evaluate(() => document.querySelectorAll('[style]').length);

console.log(JSON.stringify({
  sayacYolda: yolda, sayacBitti: bitti,
  sssOnce: once, sssOrtada: ortada, sssSonra: sonra, sssKapandi: kapandi,
  inlineStil: inline, hatalar,
}, null, 1));
await b.close();
