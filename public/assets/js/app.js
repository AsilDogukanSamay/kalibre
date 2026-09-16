/**
 * Bosch Car Service - frontend davranis katmani.
 * Modul basina tek sorumluluk, hepsi ayni baslatma noktasindan calisir.
 * Hicbir stil burada uretilmez; yalnizca CSS mimarisindeki siniflar takilip cikarilir.
 */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------------
   * Toast bildirimi - glassmorphic, tek uretici fonksiyon (DRY)
   * ---------------------------------------------------------------- */
  const Toast = (function () {
    const layer = document.getElementById('toastLayer');

    function show(type, title, body) {
      if (!layer) return;

      const el = document.createElement('div');
      el.className = 'toast ' + (type === 'success' ? 'toast-success' : 'toast-error');
      if (!reduceMotion) el.classList.add('animate-toast-in');

      const dot = document.createElement('span');
      dot.className = 'toast-dot ' + (type === 'success' ? 'toast-dot-success' : 'toast-dot-error');

      const text = document.createElement('div');
      const h = document.createElement('p');
      h.className = 'toast-title';
      h.textContent = title;
      text.appendChild(h);

      if (body) {
        const p = document.createElement('p');
        p.className = 'toast-body';
        p.textContent = body;
        text.appendChild(p);
      }

      const close = document.createElement('button');
      close.type = 'button';
      close.className = 'toast-close';
      close.setAttribute('aria-label', 'Bildirimi kapat');
      close.textContent = '✕';
      close.addEventListener('click', () => dismiss(el));

      el.append(dot, text, close);
      layer.appendChild(el);

      window.setTimeout(() => dismiss(el), type === 'success' ? 7000 : 9000);
    }

    function dismiss(el) {
      if (!el.isConnected) return;
      if (reduceMotion) { el.remove(); return; }
      el.classList.remove('animate-toast-in');
      el.classList.add('animate-toast-out');
      el.addEventListener('animationend', () => el.remove(), { once: true });
    }

    return {
      success: (t, b) => show('success', t, b),
      error: (t, b) => show('error', t, b),
    };
  })();

  /* ------------------------------------------------------------------
   * Olcum sayaclari
   * Istatistik rakamlari goruse girince sifirdan hedefe sayar. Sayfanin dili
   * olcum oldugu icin bu dekoratif degil: rakam "yazilmis" degil "okunmus"
   * gibi geliyor.
   * ---------------------------------------------------------------- */
  (function olcumSayaclari() {
    const hedefler = document.querySelectorAll('[data-sayac]');
    if (!hedefler.length) return;
    if (reduceMotion || !('IntersectionObserver' in window)) return; // deger oldugu gibi kalir

    // "1.480", "11 nokta", "36 ay" -> sayi + kalan metin
    const coz = (metin) => {
      const esleme = metin.match(/^([\d.,]+)(.*)$/);
      if (!esleme) return null;
      const sayi = Number(esleme[1].replace(/\./g, '').replace(',', '.'));
      return Number.isFinite(sayi) ? { sayi, ek: esleme[2], binlik: esleme[1].includes('.') } : null;
    };

    const bicim = (deger, binlik) => (binlik
      ? Math.round(deger).toLocaleString('tr-TR')
      : String(Math.round(deger)));

    const say = (el, hedef) => {
      const sure = 1100;
      const basla = performance.now();

      const kare = (an) => {
        const t = Math.min(1, (an - basla) / sure);
        // Sona dogru yavaslayan egri; sayac "yerine oturuyor" hissi verir.
        const yumusak = 1 - Math.pow(1 - t, 3);
        el.textContent = bicim(hedef.sayi * yumusak, hedef.binlik) + hedef.ek;
        if (t < 1) requestAnimationFrame(kare);
      };

      requestAnimationFrame(kare);
    };

    const gozlemci = new IntersectionObserver((girisler) => {
      girisler.forEach((giris) => {
        if (!giris.isIntersecting) return;
        gozlemci.unobserve(giris.target);

        const hedef = coz(giris.target.textContent.trim());
        if (hedef) say(giris.target, hedef);
      });
    }, { threshold: 0.4 });

    hedefler.forEach((el) => gozlemci.observe(el));
  })();

  /* ------------------------------------------------------------------
   * SSS panelinin yumusak acilisi
   * <details> kapaliyken icerigi hic render etmedigi icin saf CSS gecisi
   * calismaz; acilma/kapanma burada yonetilir.
   *
   * Yukseklik inline stille yazilmaz: her panel icin sayfaya bir kez eklenen
   * stylesheet'e bir kural konur ve o kuralin height degeri guncellenir.
   * Surgu ve nisangahla ayni yontem.
   * ---------------------------------------------------------------- */
  (function sssAcilisi() {
    const ogeler = document.querySelectorAll('[data-faq]');
    if (!ogeler.length || reduceMotion) return;

    const sheetEl = document.createElement('style');
    document.head.appendChild(sheetEl);

    const kurallar = new Map();
    ogeler.forEach((oge) => {
      const no = oge.dataset.faq;
      const i = sheetEl.sheet.insertRule(`[data-faq="${no}"] .faq-panel { height: auto; }`, sheetEl.sheet.cssRules.length);
      kurallar.set(oge, sheetEl.sheet.cssRules[i]);
    });

    ogeler.forEach((oge) => {
      const ozet = oge.querySelector('.faq-summary');
      const panel = oge.querySelector('.faq-panel');
      const kural = kurallar.get(oge);
      if (!ozet || !panel || !kural) return;

      // Kapali baslar; acilinca yukseklik olculur.
      kural.style.height = oge.open ? 'auto' : '0px';

      let kapaniyor = false;

      /*
       * Gecis bitmezse (ornegin sekme arka planda ve transitionend hic
       * tetiklenmezse) oge asili kalirdi; bu yuzden her animasyonun bir de
       * zaman asimi var. Ikisinden hangisi once gelirse bitiris onun.
       */
      const bitirici = (isle) => {
        let calisti = false;
        const bir = () => {
          if (calisti) return;
          calisti = true;
          panel.classList.remove('faq-panel-gecis');
          isle();
        };
        panel.addEventListener('transitionend', bir, { once: true });
        window.setTimeout(bir, 500);
      };

      ozet.addEventListener('click', (olay) => {
        olay.preventDefault();
        if (kapaniyor) return;

        if (!oge.open) {
          oge.open = true;
          kural.style.height = '0px';
          const yukseklik = panel.scrollHeight;

          requestAnimationFrame(() => {
            panel.classList.add('faq-panel-gecis');
            kural.style.height = yukseklik + 'px';
          });

          // Acildiktan sonra auto'ya birakilir: icerik degisirse yukseklik
          // sabit kalmasin.
          bitirici(() => { kural.style.height = 'auto'; });
        } else {
          kapaniyor = true;
          kural.style.height = panel.scrollHeight + 'px';

          requestAnimationFrame(() => {
            panel.classList.add('faq-panel-gecis');
            kural.style.height = '0px';
          });

          // open niteligi ancak animasyon bitince kalkar; erken kalkarsa
          // tarayici icerigi aninda gizler ve gecis gorunmez.
          bitirici(() => {
            oge.open = false;
            kapaniyor = false;
          });
        }
      });
    });
  })();

  /* ------------------------------------------------------------------
   * Satir satir baslik acilisi
   * Baslik, satirlari maskenin altindan yukari kayarak geliyor.
   * innerHTML kullanilmaz: elemanlar createElement + textContent ile kurulur.
   * ---------------------------------------------------------------- */
  (function baslikAcilisi() {
    const basliklar = document.querySelectorAll('[data-satir]');
    if (!basliklar.length || reduceMotion) return;
    if (!('IntersectionObserver' in window)) return;

    /**
     * Metni satirlara boler. Karakterlerin kutu ustleri okunur; ust degistigi
     * yerde satir bitmistir. Tarayicinin gercekte nereye sardigini olcer,
     * yani genisligi tahmin etmeye calismaz.
     */
    function satirlaraBol(el) {
      const dugum = el.firstChild;
      if (!dugum || dugum.nodeType !== 3) return null;

      const metin = dugum.textContent;
      const aralik = document.createRange();
      const satirlar = [];
      let bas = 0;
      let ust = null;

      for (let i = 0; i < metin.length; i++) {
        aralik.setStart(dugum, i);
        aralik.setEnd(dugum, i + 1);
        const kutu = aralik.getBoundingClientRect();
        if (kutu.height === 0) continue;

        if (ust === null) {
          ust = kutu.top;
        } else if (Math.abs(kutu.top - ust) > 2) {
          satirlar.push(metin.slice(bas, i));
          bas = i;
          ust = kutu.top;
        }
      }
      satirlar.push(metin.slice(bas));

      const temiz = satirlar.map((t) => t.trim()).filter((t) => t !== '');
      return temiz.length > 1 ? temiz : null; // tek satirsa bolmeye gerek yok
    }

    function kur(el) {
      const orijinal = el.textContent;
      const satirlar = satirlaraBol(el);
      if (!satirlar) return null;

      el.textContent = '';
      satirlar.forEach((satir) => {
        const kap = document.createElement('span');
        kap.className = 'satir';
        const ic = document.createElement('span');
        ic.className = 'satir-ic';
        ic.textContent = satir;
        kap.appendChild(ic);
        el.appendChild(kap);
      });

      return orijinal;
    }

    const gozlemci = new IntersectionObserver((girisler) => {
      girisler.forEach((giris) => {
        if (!giris.isIntersecting) return;
        const el = giris.target;
        gozlemci.unobserve(el);

        const orijinal = kur(el);
        if (orijinal === null) {
          el.classList.add('is-in');
          return;
        }

        // Bir sonraki karede sinif takilir ki gecis calissin.
        requestAnimationFrame(() => el.classList.add('is-in'));

        /*
         * Animasyon bitince metin eski haline doner. Kalici bir DOM
         * degisikligi birakmiyoruz: yeniden boyutlandirmada satirlar
         * degisir, metin secmede parca parca kopyalanirdi.
         */
        window.setTimeout(() => { el.textContent = orijinal; }, 1400);
      });
    }, { threshold: 0, rootMargin: '0px 0px -8% 0px' });

    // Satir kutulari yazi tipine bagli; font yuklenmeden olcmek yanlis boler.
    const basla = () => basliklar.forEach((el) => gozlemci.observe(el));
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(basla);
    } else {
      basla();
    }
  })();

  /* ------------------------------------------------------------------
   * Gorunume giris
   * Bolumler ve kartlar goruse girdiginde bir kez aciliyor. Tek seferlik:
   * acilan eleman gozlemden cikariliyor, geri kaydirinca tekrar oynamiyor.
   * ---------------------------------------------------------------- */
  (function gorunumeGiris() {
    const hedefler = document.querySelectorAll('[data-reveal]');
    if (!hedefler.length) return;

    const hepsiniAc = () => hedefler.forEach((el) => el.classList.add('is-in'));

    // Hareket azaltma tercihi veya destek yoksa animasyon hic olmaz,
    // ama icerik de gizli kalmaz.
    if (reduceMotion || !('IntersectionObserver' in window)) {
      hepsiniAc();
      return;
    }

    const gozlemci = new IntersectionObserver((girisler) => {
      girisler.forEach((giris) => {
        if (!giris.isIntersecting) return;
        giris.target.classList.add('is-in');
        gozlemci.unobserve(giris.target);
      });
    }, {
      // Eleman ekranin alt kenarindan biraz iceri girince aciliyor.
      threshold: 0,
      rootMargin: '0px 0px -10% 0px',
    });

    hedefler.forEach((el) => gozlemci.observe(el));
  })();

  /* ------------------------------------------------------------------
   * Olcum nisangahi (imlec)
   * Yerli imleci gizlemez, yanina ince bir halka koyar. Tiklanabilir bir
   * hedefin uzerinde halka acilir, icindeki tikler cekilir.
   * ---------------------------------------------------------------- */
  (function imlecNisangahi() {
    const el = document.getElementById('cursor');
    if (!el) return;

    // Dokunmatik ekranda imlec yoktur; hareket azaltma tercihinde de calismaz.
    if (reduceMotion) return;
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    /*
     * Hicbir elemana style attribute'u yazilmaz. Konum, sayfaya bir kez
     * eklenen tek kurallik bir stylesheet uzerinden guncellenir; konumlandirma
     * kurali app.css icinde kalir. Surgu ile ayni yontem.
     */
    const sheetEl = document.createElement('style');
    document.head.appendChild(sheetEl);
    sheetEl.sheet.insertRule(':root { --cursor-x: -100px; --cursor-y: -100px; }', 0);
    const rule = sheetEl.sheet.cssRules[0];

    const ETKILESIMLI = 'a, button, input, textarea, select, summary, label, [role="button"]';

    let hedefX = -100;
    let hedefY = -100;
    let x = -100;
    let y = -100;
    let doner = false;

    function kare() {
      // Yumusak takip: her karede kalan mesafenin bir kismi kapanir.
      x += (hedefX - x) * 0.2;
      y += (hedefY - y) * 0.2;

      rule.style.setProperty('--cursor-x', x.toFixed(1) + 'px');
      rule.style.setProperty('--cursor-y', y.toFixed(1) + 'px');

      // Hedefe oturunca dongu kapanir; bosta rAF harcanmaz.
      if (Math.abs(hedefX - x) < 0.15 && Math.abs(hedefY - y) < 0.15) {
        doner = false;
        return;
      }
      requestAnimationFrame(kare);
    }

    function uyandir() {
      if (doner) return;
      doner = true;
      requestAnimationFrame(kare);
    }

    window.addEventListener('pointermove', (event) => {
      if (event.pointerType !== 'mouse') return;

      hedefX = event.clientX;
      hedefY = event.clientY;

      el.classList.add('cursor-gorunur');

      const hedef = event.target;
      const etkilesimli = hedef instanceof Element && hedef.closest(ETKILESIMLI) !== null;
      el.classList.toggle('cursor-etkin', etkilesimli);

      uyandir();
    }, { passive: true });

    window.addEventListener('pointerdown', () => el.classList.add('cursor-basili'), { passive: true });
    window.addEventListener('pointerup', () => el.classList.remove('cursor-basili'), { passive: true });

    // Pencereden cikinca nisangah kaybolur, geri gelince ilk harekette doner.
    document.addEventListener('mouseleave', () => el.classList.remove('cursor-gorunur'));
    window.addEventListener('blur', () => el.classList.remove('cursor-gorunur'));
  })();

  /* ------------------------------------------------------------------
   * Iletisim formu - fetch tabanli, sayfa yenilenmez
   * ---------------------------------------------------------------- */
  (function contactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const button = form.querySelector('[data-submit]');
    const label = form.querySelector('[data-submit-label]');
    const spinner = form.querySelector('[data-submit-spinner]');

    function clearErrors() {
      form.querySelectorAll('[data-error-for]').forEach((node) => {
        node.textContent = '';
        node.classList.remove('field-error-visible');
      });
      form.querySelectorAll('.field-input-invalid').forEach((node) => {
        node.classList.remove('field-input-invalid');
        node.removeAttribute('aria-invalid');
      });
    }

    function paintErrors(errors) {
      let first = null;
      Object.keys(errors || {}).forEach((field) => {
        const message = form.querySelector('[data-error-for="' + field + '"]');
        const input = form.elements[field];
        if (message) {
          message.textContent = errors[field];
          message.classList.add('field-error-visible');
        }
        if (input) {
          input.classList.add('field-input-invalid');
          input.setAttribute('aria-invalid', 'true');
          if (!first) first = input;
        }
      });
      if (first) first.focus();
    }

    function busy(state) {
      button.disabled = state;
      button.setAttribute('aria-busy', state ? 'true' : 'false');
      label.textContent = state ? 'Gönderiliyor' : 'Talebi gönder';
      spinner.classList.toggle('hidden', !state);
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      clearErrors();
      busy(true);

      const payload = Object.fromEntries(new FormData(form).entries());

      try {
        const response = await fetch('/api/contact', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));

        if (response.ok && result.ok) {
          form.reset();
          Toast.success(
            result.message || 'Talebiniz alındı.',
            result.reference ? 'Referans numaranız: ' + result.reference : ''
          );
        } else {
          paintErrors(result.errors);
          Toast.error(result.message || 'Talebiniz gönderilemedi.', '');
        }
      } catch (error) {
        Toast.error('Bağlantı kurulamadı.', 'İnternet bağlantınızı kontrol edip tekrar deneyin.');
      } finally {
        busy(false);
      }
    });
  })();

  /* ------------------------------------------------------------------
   * Oncesi / sonrasi surgusu
   * ---------------------------------------------------------------- */
  (function compareSlider() {
    const roots = document.querySelectorAll('[data-compare]');
    if (!roots.length) return;

    /*
     * Hicbir elemana style attribute'u yazilmaz. Konum degeri, sayfaya bir kez
     * eklenen tek kurallik bir stylesheet uzerinden guncellenir; kirpma ve
     * konumlandirma kurallari app.css icinde kalir.
     */
    const sheetEl = document.createElement('style');
    document.head.appendChild(sheetEl);
    sheetEl.sheet.insertRule(':root { --compare-pos: 50%; }', 0);
    const rule = sheetEl.sheet.cssRules[0];

    roots.forEach((root) => {
      const range = root.querySelector('[data-compare-range]');
      if (!range) return;

      const paint = () => rule.style.setProperty('--compare-pos', range.value + '%');
      range.addEventListener('input', paint);
      paint();
    });
  })();

  /* ------------------------------------------------------------------
   * Hero arka plan videosu
   * Veri tasarrufu, hareket azaltma veya dar ekranda hic indirilmez.
   * ---------------------------------------------------------------- */
  (function heroVideo() {
    const bg = document.getElementById('heroBg');
    const video = document.getElementById('heroVideo');
    if (!bg || !video) return;

    const conn = navigator.connection || {};
    // Video kaynagi HTML'de data-src olarak durur. Kosullar saglanmazsa
    // src hic atanmaz, yani tarayici tek bir bayt bile indirmez.
    if (reduceMotion || conn.saveData === true || window.innerWidth < 640) {
      return;
    }

    video.preload = 'auto';
    video.src = video.dataset.src;
    video.addEventListener('canplay', () => {
      const played = video.play();
      if (played && played.then) {
        played.then(() => bg.classList.add('hero-bg-playing')).catch(() => {});
      } else {
        bg.classList.add('hero-bg-playing');
      }
    }, { once: true });
  })();

  /* ------------------------------------------------------------------
   * Scroll ile ilerleyen duzeltme pasosu
   * scroll dinleyicisi yok: IntersectionObserver bolum gorunurken
   * bir rAF dongusu acar, bolum cikinca kapatir.
   * Konum degeri tek kurallik stylesheet uzerinden tasinir.
   * ---------------------------------------------------------------- */
  (function scrubSection() {
    const track = document.getElementById('scrubTrack');
    const stage = document.getElementById('scrubStage');
    const video = document.getElementById('scrubVideo');
    const micron = document.getElementById('gaugeMicron');
    const gloss = document.getElementById('gaugeGloss');
    const percent = document.getElementById('scrubPercent');
    if (!track || !stage) return;

    const sheetEl = document.createElement('style');
    document.head.appendChild(sheetEl);
    sheetEl.sheet.insertRule(':root { --scrub-p: 0; }', 0);
    const rule = sheetEl.sheet.cssRules[0];

    // Ornek olcum araligi. Gercek verilerle degistirilebilir.
    const MICRON = [138, 129];
    const GLOSS = [41, 94];

    // Ayni kural: dar ekranda veya veri tasarrufunda src hic atanmaz.
    const conn2 = navigator.connection || {};
    const videoIzinli = video && !reduceMotion && conn2.saveData !== true && window.innerWidth >= 640;

    if (videoIzinli) {
      const enable = () => {
        if (video.duration > 0) stage.classList.add('scrub-stage-video');
      };
      // Yerel dosyada metadata dinleyici baglanmadan once gelebiliyor;
      // o yuzden hazir olma durumu ayrica kontrol edilir.
      video.addEventListener('loadedmetadata', enable);
      video.addEventListener('error', () => stage.classList.remove('scrub-stage-video'));
      video.preload = 'auto';
      video.src = video.dataset.src;
      if (video.readyState >= 1) enable();
    }

    const lerp = (a, b, p) => Math.round(a + (b - a) * p);

    function apply(p) {
      rule.style.setProperty('--scrub-p', p.toFixed(4));
      // Yalnizca sayi guncellenir; birim etiketi sablonda sabit durur.
      // Boylece istemci tarafinda innerHTML hic kullanilmaz.
      if (micron) micron.textContent = String(lerp(MICRON[0], MICRON[1], p));
      if (gloss) gloss.textContent = String(lerp(GLOSS[0], GLOSS[1], p));
      if (percent) percent.textContent = '%' + Math.round(p * 100);
      if (stage.classList.contains('scrub-stage-video') && video.duration) {
        video.currentTime = Math.min(video.duration - 0.05, video.duration * p);
      }
    }

    if (reduceMotion) { apply(1); return; }

    let running = false;
    function frame() {
      if (!running) return;
      const r = track.getBoundingClientRect();
      const span = r.height - window.innerHeight;
      const p = span > 0 ? -r.top / span : 0;
      apply(Math.max(0, Math.min(1, p)));
      requestAnimationFrame(frame);
    }

    new IntersectionObserver((entries) => {
      const visible = entries[0].isIntersecting;
      if (visible && !running) { running = true; requestAnimationFrame(frame); }
      else if (!visible) { running = false; }
    }, { threshold: 0 }).observe(track);

    apply(0);
  })();

  /* ------------------------------------------------------------------
   * Kampanya geri sayimi
   * ---------------------------------------------------------------- */
  (function countdown() {
    const root = document.querySelector('[data-countdown]');
    if (!root) return;

    const target = new Date(root.getAttribute('data-countdown')).getTime();
    if (Number.isNaN(target)) return;

    const cells = root.querySelector('[data-countdown-cells]');
    const expired = root.querySelector('[data-countdown-expired]');
    const out = {
      days: root.querySelector('[data-cd="days"]'),
      hours: root.querySelector('[data-cd="hours"]'),
      minutes: root.querySelector('[data-cd="minutes"]'),
      seconds: root.querySelector('[data-cd="seconds"]'),
    };

    const pad = (n) => String(n).padStart(2, '0');

    function tick() {
      const diff = target - Date.now();

      if (diff <= 0) {
        cells.classList.add('hidden');
        if (expired) expired.classList.remove('hidden');
        window.clearInterval(timer);
        return;
      }

      const s = Math.floor(diff / 1000);
      out.days.textContent = pad(Math.floor(s / 86400));
      out.hours.textContent = pad(Math.floor((s % 86400) / 3600));
      out.minutes.textContent = pad(Math.floor((s % 3600) / 60));
      out.seconds.textContent = pad(s % 60);
    }

    tick();
    const timer = window.setInterval(tick, 1000);
  })();
})();
