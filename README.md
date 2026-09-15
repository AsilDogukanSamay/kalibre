# Glassmorphic Landing Page & İletişim Modülü

İstanbul Maslak'ta boya düzeltme ve seramik kaplama atölyesi için landing page.
Tailwind CSS ile glassmorphic arayüz, saf PHP 8.4 ile OOP/MVC backend,
AJAX tabanlı iletişim formu ve MySQL kaydı.

---

## 0. İki sayfa

| Yol | Ne |
|---|---|
| `/` | Ürünün kendisi. Landing page. |
| `/case` | Ürün kararları. Problem, alınan kararlar ve bedelleri, canlı tasarım sistemi, mimari, ölçülen sonuçlar. |

`/case` sayfası ekran görüntüsü değil: gösterdiği token'lar ve cam yüzeyler,
sitenin gerçekten kullandığı CSS sınıflarıyla render edilir.

---

## 1. Görsel kimlik ve marka katmanı

Tasarım sistemi **marka bağımsızdır**. Renkler CSS değişkenlerinde tutulur,
Tailwind token'ları o değişkenleri okur. Marka değiştirmek tek bir `.env` satırıdır;
hiçbir sınıf, şablon veya bileşen değişmez.

```ini
APP_BRAND=bosch     # veya: kalibre
```

| Tema | Logo | Primary | Secondary | Accent |
|---|---|---|---|---|
| `bosch` (varsayılan) | Gerçek Bosch amblemi, orijinal vektör yolu | `#EA0016` | `#00509D` | `#008ECF` |
| `kalibre` | Atölyenin kendi markası | `#4B7BFF` | `#3D6BF0` | `#7A9CFF` |

**Neden iki tema:** Ajans işinde aynı iskelet farklı markalara giydirilir. Sınıf
adlarına marka gömmek her projede yeniden yazmak demektir. `app/Support/Brand.php`
tek kaynak, `:root[data-brand="..."]` tek geçiş noktası.

Bosch teması etkinken sayfa altında, çalışmanın bağımsız bir prototip olduğunu ve
marka haklarının Robert Bosch GmbH'ye ait olduğunu belirten bilgilendirme
otomatik görünür.

### Palet gerekçesi

| Rol | Token | Değer | Gerekçe |
|---|---|---|---|
| Primary | `brand` | markaya göre | Aktif markanın kurumsal rengi. Sayfanın **tek** aksanı. |
| Primary (koyu) | `brand-dark` | markaya göre | Hover durumu |
| Zemin | `surface-900` | `#101214` | Saf siyah değil, grafit |
| Yüzey | `surface-800` / `700` | `#15181B` / `#1B1F24` | Yükseltilmiş katmanlar |
| Metin | `ink` | `#E9ECEF` | Birincil |
| Metin (ikincil) | `ink-muted` | `#8B949C` | Koyu zeminde AA geçer |
| Hata | `warn` | `#FF9A8B` | Form hataları, hata toast'ı |
| Başarı | `good` | `#5BD08A` | Başarı toast'ı |

Zemin ve metin skalası her iki markada ortaktır; yalnızca `brand-*` değişir.

**Aksan kilidi:** sayfanın tamamında tek aksan rengi kullanılır.
**Tema kilidi:** sayfa tek modda (koyu) çalışır, bölümler arası mod değişmez.

### Tipografi

Tek yazı tipi ailesi: **Archivo** (variable). İkinci bir yazı tipi yerine
**genişlik ekseni** ikinci bir ses olarak kullanılır:

| Rol | `wdth` | `wght` | Sınıf |
|---|---|---|---|
| Başlık | 118 | 700 | `.display` |
| Gövde | 92 | 400 | `body` |
| Ölçüm okumaları | 70 | 600 | `.readout` (tablo rakamları ile) |

### Köşe yarıçapı

Tek ölçek: `3px` (`rounded-glass`). Atölye dili keskindir; istisna yalnızca
pill butonlar ve yuvarlak göstergelerdir (`rounded-pill`).

---

## 2. Mimari

Framework kullanılmadı; **saf PHP 8.4 ile OOP + MVC** katmanlı yapı.
Composer bağımlılığı yok, PSR-4 uyumlu kendi autoloader'ı var.

```
kalibre-landing/
├── public/                  ← web kökü (sunucu buraya bakar)
│   ├── index.php            ← tek giriş noktası (front controller)
│   └── assets/{css,js,img,video}
├── app/
│   ├── Core/                ← çerçeve katmanı
│   │   ├── Autoloader.php   PSR-4 autoloader
│   │   ├── Env.php          .env okuyucu
│   │   ├── Database.php     PDO tekil bağlantı
│   │   ├── Router.php       metot + yol eşleştirme
│   │   ├── Request.php      HTTP isteği sarmalayıcı
│   │   ├── Response.php     JSON / HTML çıktı sözleşmesi
│   │   ├── Validator.php    kural tabanlı doğrulama
│   │   ├── Controller.php   soyut taban
│   │   ├── View.php         şablon motoru + XSS kaçışı
│   │   └── helpers.php      e() / partial() / asset()
│   ├── Controllers/         HomeController, CaseStudyController, ContactController
│   ├── Models/              ContactMessage (veri erişimi)
│   ├── Support/             Brand (marka katmanı), SiteContent, CaseStudy
│   └── Views/               layouts, partials, errors
├── database/schema.sql
├── resources/css/app.css    ← Tailwind kaynağı + component katmanı
├── tailwind.config.js       ← kurumsal kimlik yapılandırması
├── tests/run.php            ← bağımlılıksız duman testleri
├── _eski/index.static.html  ← bu dönüşümden önceki tek dosyalık sürüm
└── .env                     ← versiyonlanmaz
```

**İstek akışı:** `public/index.php` → `Router` → `Controller` → (`Validator` / `Model`)
→ `Response` veya `View`. Spagetti PHP yoktur; hiçbir dosyada HTML ile sorgu iç içe geçmez.

**Katman sorumlulukları:** doğrulama `Validator`'da, SQL yalnızca `Models/` altında,
çıktı kaçışı yalnızca `View`'da, yapılandırma yalnızca `Env`'de.

---

## 3. Veritabanı şeması

```sql
CREATE TABLE `contact_messages` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`  VARCHAR(120)  NOT NULL,
    `email`      VARCHAR(180)  NOT NULL,
    `phone`      VARCHAR(32)   NOT NULL,
    `message`    TEXT          NOT NULL,
    `ip_address` VARCHAR(45)   NOT NULL DEFAULT '',
    `user_agent` VARCHAR(255)  NOT NULL DEFAULT '',
    `status`     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_email`      (`email`),
    KEY `idx_ip_created` (`ip_address`, `created_at`),
    KEY `idx_status`     (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- `utf8mb4` — Türkçe karakter güvenliği.
- `ip_address` 45 karakter — IPv6 uzunluğu.
- `idx_ip_created` — spam freni sorgusu (ip + zaman aralığı) tam bu indeksi kullanır.
- `status` — talebin operasyonel takibi; panel eklenirse şema değişmez.

---

## 4. .env yapılandırması

Hassas bilgi kod tabanında tutulmaz. `.env` `.gitignore` içindedir,
`.env.example` şablon olarak versiyonlanır.

```ini
APP_NAME="Kalibre"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Europe/Istanbul

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kalibre
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

CONTACT_RATE_LIMIT=5
CONTACT_RATE_WINDOW_MINUTES=10

CAMPAIGN_ENDS_AT=2026-12-31T23:59:59+03:00
```

`CAMPAIGN_ENDS_AT` boş bırakılırsa geri sayım bölümü hiç render edilmez.

---

## 5. Güvenlik

| Önlem | Nerede |
|---|---|
| **SQL Injection** | `Models/ContactMessage.php` — tüm sorgular PDO named prepared statement. `ATTR_EMULATE_PREPARES => false` ile gerçek sunucu tarafı hazırlama. |
| **XSS** | `View::e()` / `e()` — `htmlspecialchars` + `ENT_QUOTES` + `ENT_SUBSTITUTE`. Şablonda kaçışsız dinamik değer basılmaz. İstemcide `textContent` kullanılır, `innerHTML` kullanılmaz. |
| **Validation** | `Core/Validator.php` — `required`, `email`, `phone`, `min`, `max`. Kontrol karakterleri temizlenir, değerler trim edilir. |
| **Hata sızıntısı** | PDO istisnası yakalanır, kullanıcıya genel mesaj döner, teknik detay `error_log`'a yazılır. |
| **Spam** | Bal küpü (honeypot) + IP bazlı oran sınırı (varsayılan 10 dakikada 5). |
| **Başlıklar** | `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`. |
| **Dizin güvenliği** | Web kökü `public/`. `app/`, `.env`, `database/` web'den erişilemez. |

---

## 6. Frontend

- **Tailwind CSS 3.4**, `tailwind.config.js` kurumsal kimliğe göre özelleştirilmiş.
- **Hiçbir HTML etiketinde `style="..."` attribute'u yoktur.** Test bunu otomatik
  doğrular; canlı DOM'da da `document.querySelectorAll('[style]').length === 0`.
- Dinamik konum değerleri (sürgü, scroll ilerlemesi) inline stil ile değil, sayfaya
  bir kez eklenen **tek kurallık bir stylesheet** üzerinden taşınan CSS custom
  property ile yönetilir (`--compare-pos`, `--scrub-p`). Kırpma ve konumlandırma
  kuralları `resources/css/app.css` içinde kalır.
- Glassmorphism tek kaynaktan türer: `.glass`, `.glass-strong`, `.glass-soft`,
  `.glass-sheen`, `.glass-card`. Yeni cam yüzey için kural yazılmaz, sınıf kullanılır (DRY).
- `prefers-reduced-motion` ve `prefers-reduced-transparency` tercihlerine uyulur.
- Klavye erişilebilirliği: `focus-visible` halkası, "İçeriğe atla" bağlantısı,
  hatalı alanlarda `aria-invalid`, toast'ta `aria-live="polite"`.

### Erişilebilirlik denetimi

Sayfadaki her metin, gerçek zemin rengine karşı ölçülerek denetlendi.
İlk taramada **33 eleman** WCAG AA eşiğinin altındaydı; iki sistematik sebep vardı:

1. **Marka kırmızısı koyu zeminde metin olarak yetersiz.** `#EA0016` grafit üzerinde
   4,03:1 veriyor. Çözüm renk değiştirmek değil, **ayrı bir metin token'ı** eklemek
   oldu: `--brand-text`. Dolgu, kenarlık ve ikonlar kurumsal `--brand` rengini
   kullanmaya devam ediyor; yalnızca yazı açılmış varyanta geçti.
   Bosch 5,05:1 · Kalibre 7,18:1.
2. **`--ink-faint` çok koyuydu** (4,11:1) ve 10px etiketlerde kullanılıyordu.
   5,12:1'e açıldı, en küçük etiket 11px'e çıkarıldı.

Ayrıca düzeltilenler:

| Bulgu | Önce | Sonra |
|---|---|---|
| SSS başlığı tıklama alanı | 24 px | 64 px (mobilde 88 px) |
| En uzun satır | 149 karakter | 66 karakter |
| Geri sayım, 360 px | 3+1 sarıyordu | tek satır |
| AA eşiği altındaki metin | 33 | **0** |

Doğrulanan diğer maddeler: tek `h1`, başlık hiyerarşisinde atlama yok, tüm
görsellerde `alt`, landmark'lar (`header`/`nav`/`main`/`footer`) yerinde,
360 px'de yatay kaydırma yok.

### Yerleşim kuralı: kısıtlı metin, taşan görsel

Öncesi/sonrası bölümü önce `.shell` içindeydi (1320 px), hemen altındaki scroll
bölümü ise tam ekrandı. İki gösterim bölümü farklı dil konuşuyordu ve asıl
fotoğraf küçük kalan taraftaydı. 1880 px'lik ekranda ölçüldü: görsel 1254 px,
kenarlarda 570 px boşluk.

Editoryal kurala geçildi: **metin okunabilir genişlikte kalır, görsel tam ekrana
taşar.** Başlık ve alt bilgi `.shell` içinde, görsel `.shell` dışında.

| | Önce | Sonra |
|---|---|---|
| Görsel genişliği (1880 px ekran) | 1254 px | 1865 px |
| Scroll bölümüyle tutarlılık | yok | ikisi de tam ekran |
| Mobilde karşılaştırma yüksekliği | 220 px (16:9) | 260 px (3:2) |

Yükseklik `max-h-[80dvh]` ile sınırlandı; aksi halde geniş ekranda 16:9 oran
1057 px'e çıkıp görünüm alanını aşıyordu.

### Hero yazısının okunabilirliği

Hero'da yazı hareketli video üzerinde durur. Perde (scrim) göz kararı değil,
ölçülerek ayarlanmıştır: videonun 10 karesi × 4 ekran genişliği üzerinden en kötü
kontrast **5.2:1** (WCAG AA eşiği 4.5). Perdeyi bir kademe açmak bu değeri 3.9'a
düşürür ve alt paragraf eşiğin altında kalır. Ayrıca perdeden bağımsız ikinci
güvence olarak yazıya ince bir gölge uygulanmıştır.

Perde 1320px altında yataydan dikeye döner: `.shell` maksimum genişliğine o noktada
ulaşır, altında yazı bloğu tam genişliğe yayıldığı için yatay perde işe yaramaz.

---

## 7. Ekstra modüller ve gerekçeleri

| Modül | Gerekçe |
|---|---|
| **Hero arka plan videosu** | Sessiz, 20 sn dikişsiz döngü (ileri-geri birleştirilmiş). Cam yüzeylerin hareketli görüntü üzerinde durması glassmorphism'in en güçlü göründüğü senaryo. |
| **Scroll ile sürülen paso videosu** | Scroll ilerlemesi videonun zaman çizgisine bağlanır, mikron ve parlaklık sayaçları eşzamanlı sayar. `scroll` dinleyicisi yoktur: IntersectionObserver bölüm görünürken bir rAF döngüsü açar, çıkınca kapatır. Video 3 karede bir keyframe ile kodlanmıştır, seek pürüzsüzdür. |
| **Öncesi/sonrası sürgüsü** | Hizmetin çıktısını anlatmak yerine gösterir. `range` input kullanıldığı için klavye ve ekran okuyucu desteği hazır gelir. |
| **Kampanya geri sayımı** | Aciliyet duygusu. Bitiş tarihi `.env`'den; süresi dolunca sayaç gizlenip bilgilendirme mesajına döner. |
| **WhatsApp destek butonu** | Türkiye'de servis randevusu için birincil kanal. Hazır mesaj metniyle açılır. |
| **Referans kartları** | Sosyal kanıt. Yıldız derecelendirmesi ve araç modeli, genel ifadelere göre daha inandırıcı. |
| **Honeypot + oran sınırı** | Bot gönderimlerini CAPTCHA eklemeden azaltır. |
| **Referans numarası** | Başarılı gönderimde `KLB-004271` biçiminde numara döner. Kullanıcıya somut geri bildirim, operasyona takip anahtarı. |
| **`SiteContent` sınıfı** | Tüm metinler tek kaynakta. Şablonlarda dizi tekrarı yok, içerik güncellemesi HTML'e dokunmadan yapılır. |

### Mobil veri koruması

640px altında iki video da **hiç indirilmez**; poster görselleri kalır ve scroll
bölümü sayaç + ışık şeridi efektiyle çalışmaya devam eder. `saveData` açıksa veya
`prefers-reduced-motion` seçiliyse de indirilmez.

Uygulama detayı önemli: video kaynağı HTML'de `src` değil **`data-src`** olarak durur.
`src` ile yazılsaydı tarayıcı sayfayı ayrıştırırken indirmeye başlar, `defer` ile
çalışan JavaScript devreye girdiğinde iş işten geçmiş olurdu. Kaynak yalnızca
koşullar sağlandığında atanır, aksi halde tek bayt inmez.

Ölçülen: mobilde (375px) **393 KB**, hiç video isteği yok. Masaüstünde 8 MB,
videolar poster yüklendikten sonra iniyor, ilk boyamayı etkilemiyor.

---

## 8. Kurulum

```bash
npm install
cp .env.example .env              # DB bilgilerini düzenleyin
mysql -u root -p < database/schema.sql
npm run build                     # geliştirme için: npm run dev
npm run serve                     # http://127.0.0.1:5174
```

MySQL kurulu değilse taşınabilir MariaDB ile de çalışır (yönetici izni gerekmez);
ayrıntı `HANDOFF.md` içinde.

Üretimde web sunucusunun kök dizini **`public/`** olmalıdır.

---

## 9. Testler

```bash
npm test          # veya: php tests/run.php
```

Harici bağımlılık gerektirmez. Veri erişim katmanı sahte bir PDO ile test edilir,
böylece **MySQL kurulu olmadan da** prepared statement kullanıldığı ve kullanıcı
girdisinin SQL metnine birleştirilmediği doğrulanabilir.

Mevcut durum: **22 test, hepsi geçiyor.**

### Rotalar

| Metot | Yol | Denetleyici |
|---|---|---|
| GET | `/` | `HomeController@index` |
| GET | `/case` | `CaseStudyController@index` |
| POST | `/api/contact` | `ContactController@store` |

### API sözleşmesi

`POST /api/contact` — `Content-Type: application/json`

| Durum | HTTP | Gövde |
|---|---|---|
| Başarılı | 200 | `{"ok":true,"message":"...","reference":"KLB-004271"}` |
| Doğrulama hatası | 422 | `{"ok":false,"message":"...","errors":{"email":"..."}}` |
| Oran sınırı | 429 | `{"ok":false,"message":"...","errors":[]}` |
| Veritabanı erişilemez | 503 | `{"ok":false,"message":"...","errors":[]}` |

---

## 10. Uçtan uca doğrulama

Form akışı gerçek bir veritabanına karşı çalıştırıldı (MariaDB 11.4, PDO `mysql`
sürücüsü). Ölçülen sonuçlar:

| Senaryo | Sonuç |
|---|---|
| Tarayıcıdan form gönderimi | 200, `KLB-000001` referansı, kayıt tabloda |
| Türkçe karakter (`Melis Arıkan`, `için`) | `utf8mb4` ile bozulmadan kaydedildi |
| SQL injection denemesi (`'); DROP TABLE ...`) | Düz metin olarak kaydedildi, tablo bozulmadı |
| Boş form | 422 + dört alan için ayrı hata mesajı |
| Geçersiz e-posta | 422, yalnızca `email` alanı işaretlendi |
| Honeypot dolu (bot) | 200, kayıt oluşturulmadı |
| Oran sınırı (10 dk / 5) | 5. kayıttan sonra 429 |
| Veritabanı kapalı | 503, kontrollü mesaj, uygulama çökmüyor |

## 11. Önceki sürüm

Bu dönüşümden önceki tek dosyalık statik sürüm `_eski/index.static.html`
içinde korunmuştur. Görseller ve videolar `public/assets/` altına taşınmıştır.
