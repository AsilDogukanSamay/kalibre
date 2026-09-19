/**
 * Chrome'un yeri isletim sistemine gore degisir.
 *
 * Dort denetim betiginde de varsayilan Windows yoluna CAKILIYDI:
 *   process.env.CHROME ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe'
 *
 * Proje Windows'ta gelistirildigi icin bu fark edilmiyordu. macOS ya da
 * Linux'taki biri `npm run denetim` dediginde playwright "executable doesn't
 * exist" diye kaliyor ve hata mesaji ne yapmasi gerektigini soylemiyor.
 * Depoyu klonlayip calistiracak kisinin Windows kullandigini varsayamayiz.
 *
 * Sira: CHROME ortam degiskeni -> isletim sisteminin bilinen yollari.
 * Hicbiri bulunamazsa, ne yapilacagini SOYLEYEN bir hata firlatilir.
 */
import { existsSync } from 'node:fs';

const ADAYLAR = {
  win32: [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    `${process.env.LOCALAPPDATA ?? ''}/Google/Chrome/Application/chrome.exe`,
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  ],
  darwin: [
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Chromium.app/Contents/MacOS/Chromium',
    '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
  ],
  linux: [
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
    '/snap/bin/chromium',
  ],
};

/** @returns {string} Calistirilabilir Chrome (ya da Chromium/Edge) yolu. */
export function chromeYolu() {
  if (process.env.CHROME) return process.env.CHROME;

  for (const yol of ADAYLAR[process.platform] ?? []) {
    if (yol && existsSync(yol)) return yol;
  }

  throw new Error(
    'Chrome bulunamadi. Denetimler olcum yapmak icin yerel bir Chrome acar.\n' +
    'Kurulu ise yolunu verin:\n' +
    '  CHROME="/yol/chrome" npm run denetim\n' +
    `Aranan yerler (${process.platform}):\n  ` +
    (ADAYLAR[process.platform] ?? ['(bu isletim sistemi icin aday yok)']).join('\n  ')
  );
}
