/**
 * KALİBRE kurumsal kimliğine göre özelleştirilmiş Tailwind yapılandırması.
 *
 * Palet kaynağı: atölyenin kendi görsel dili.
 *   brand  #4B7BFF  primary  — detaycıların çizik bulmak için kullandığı
 *                             mavi inceleme lambasının rengi
 *   ink   #101214  zemin    — saf siyah değil, grafit
 *   paper #E9ECEF  metin
 *
 * Tipografi: tek aile (Archivo variable). İkinci bir yazı tipi yerine
 * genişlik ekseni (wdth) kullanılır: başlıklar 118, gövde 92, ölçümler 70.
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
          alt:     'rgb(var(--brand-alt) / <alpha-value>)',
          text:    'rgb(var(--brand-text) / <alpha-value>)',
        },
        surface: {
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
        brand:      '0 10px 30px rgb(var(--brand) / 0.26)',
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
        'pulse-ring':'pulse-ring 2.4s cubic-bezier(0.4,0,0.6,1) infinite',
      },
    },
  },
  plugins: [],
};
