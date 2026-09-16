/**
 * Tailwind yapilandirmasi - kurumsal kimlige gore ozellestirilmis.
 *
 * ETKIN KIMLIK: Bosch Car Service  (.env -> APP_BRAND=bosch)
 *
 *   Rol        Kod        Token                Nerede kullanilir
 *   primary    #EA0016    brand                Buton, vurgu, ikon, kenarlik
 *   hover      #B8000F    brand-dark           Buton hover
 *   secondary  #00509D    brand-secondary      Olcum gostergesinin kanali
 *   accent     #008ECF    brand-accent         Canli olcum degeri, isik seridi
 *   metin      #FF374E    brand-text           Koyu zeminde marka renkli YAZI
 *
 * Ikinci kimlik: Kalibre  (APP_BRAND=kalibre)
 *   primary #4B7BFF · secondary #3D6BF0 · accent #7A9CFF
 *
 * NEDEN RENKLER BURADA HEX OLARAK YAZILI DEGIL
 * Token'lar CSS degiskenlerini okur (`rgb(var(--brand) / <alpha-value>)`).
 * Hex degerleri resources/css/app.css icindeki :root bloklarinda durur ve
 * `:root[data-brand="..."]` ile degisir. Boylece marka degistirmek tek bir
 * .env satiridir; hicbir Tailwind sinifi, sablon veya bilesen degismez.
 * Alfa destegi korunur: bg-brand/20, text-brand-accent/60 calisir.
 *
 * NEDEN AYRI BIR brand-text TOKEN'I VAR
 * #EA0016 koyu zeminde METIN olarak 4,03:1 veriyor, WCAG AA esigi 4,5.
 * Dolgu ve ikonlar kurumsal rengi kullanmaya devam eder; yalnizca yazi
 * acilmis varyanta gecer. Olcum ayrintisi README bolum 6'da.
 *
 * TIPOGRAFI
 * Tek aile: Archivo (variable). Bosch Sans lisansli oldugu icin kullanilmadi.
 * Ikinci bir yazi tipi yerine genislik ekseni (wdth) ikinci ses olarak
 * kullanilir: baslik 112, govde 96, olcum okumasi 72.
 */
module.exports = {
  content: [
    './app/Views/**/*.php',
    './public/assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Marka renkleri CSS degiskenlerinden okunur; tema degistirmek icin
        // hicbir Tailwind sinifi degismez, yalnizca :root degiskenleri degisir.
        brand: {
          DEFAULT: 'rgb(var(--brand) / <alpha-value>)',
          dark:    'rgb(var(--brand-dark) / <alpha-value>)',
          tint:    'rgb(var(--brand-tint) / <alpha-value>)',
          secondary: 'rgb(var(--brand-secondary) / <alpha-value>)',
          accent:    'rgb(var(--brand-accent) / <alpha-value>)',
          text:    'rgb(var(--brand-text) / <alpha-value>)',
        },
        surface: {
          950: 'rgb(var(--surface-950) / <alpha-value>)',
          900: 'rgb(var(--surface-900) / <alpha-value>)',
          800: 'rgb(var(--surface-800) / <alpha-value>)',
          700: 'rgb(var(--surface-700) / <alpha-value>)',
          600: 'rgb(var(--surface-600) / <alpha-value>)',
        },
        line: {
          DEFAULT: 'rgb(var(--line) / <alpha-value>)',
          strong:  'rgb(var(--line-strong) / <alpha-value>)',
        },
        ink: {
          DEFAULT: 'rgb(var(--ink) / <alpha-value>)',
          muted:   'rgb(var(--ink-muted) / <alpha-value>)',
          faint:   'rgb(var(--ink-faint) / <alpha-value>)',
        },
        warn: 'rgb(var(--warn) / <alpha-value>)',
        good: 'rgb(var(--good) / <alpha-value>)',
      },
      fontFamily: {
        sans: ['Archivo', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
      },
      borderRadius: {
        glass: '3px',       // atölye dili: keskin, tek yarıçap ölçeği
        pill:  '999px',
      },
      boxShadow: {
        glass:      '0 8px 32px rgba(4, 8, 14, 0.42), inset 0 1px 0 rgba(255,255,255,0.08)',
        'glass-lg': '0 24px 64px rgba(4, 8, 14, 0.55), inset 0 1px 0 rgba(255,255,255,0.12)',
        // Negatif yayilim bilincli: 0 10px 30px hicbir yayilim kisitlamasi
        // olmadan butonun DORT YANINDA kirmizi bir hale birakiyordu.
        // Eksi yayilim golgeyi iceri ceker, asagi yonlu ve olculu kalir.
        brand:      '0 8px 20px -6px rgb(var(--brand) / 0.45)',
      },
      backdropBlur: {
        glass: '16px',
      },
      keyframes: {
        'toast-in':  { '0%': { opacity: '0', transform: 'translateY(-10px)' }, '100%': { opacity: '1', transform: 'none' } },
        'toast-out': { '0%': { opacity: '1', transform: 'none' }, '100%': { opacity: '0', transform: 'translateY(-10px)' } },
        rise:        { '0%': { opacity: '0', transform: 'translateY(14px)' }, '100%': { opacity: '1', transform: 'none' } },
        'pulse-ring':{ '0%': { transform: 'scale(0.9)', opacity: '0.65' }, '70%,100%': { transform: 'scale(1.6)', opacity: '0' } },
      },
      animation: {
        'toast-in':  'toast-in 240ms cubic-bezier(0.16,1,0.3,1) forwards',
        'toast-out': 'toast-out 200ms cubic-bezier(0.4,0,1,1) forwards',
        'rise-1':    'rise 750ms cubic-bezier(0.16,1,0.3,1) 0.05s forwards',
        'rise-2':    'rise 750ms cubic-bezier(0.16,1,0.3,1) 0.14s forwards',
        'rise-3':    'rise 750ms cubic-bezier(0.16,1,0.3,1) 0.23s forwards',
        'rise-4':    'rise 750ms cubic-bezier(0.16,1,0.3,1) 0.34s forwards',
        'pulse-ring':'pulse-ring 2.4s cubic-bezier(0.4,0,0.6,1) infinite',
      },
    },
  },
  plugins: [],
};
