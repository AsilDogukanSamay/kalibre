# Kararlar ve ölçümler

Bu dosya, `README.md`'nin ayrıntılı eşlikçisidir. README projeyi kısa ve net
anlatır; burada her kararın **gerekçesi**, denenip bırakılan alternatifler ve
ölçüm sonuçları durur.

Canlı karşılığı `/case` sayfasıdır: orada gösterilen token'lar ve cam yüzeyler
ekran görüntüsü değil, sitenin gerçekten kullandığı CSS sınıflarıyla render edilir.

---


İstanbul Maslak'ta boya düzeltme ve seramik kaplama atölyesi için landing page.
Tailwind CSS ile glassmorphic arayüz, saf PHP 8.4 ile OOP/MVC backend,
AJAX tabanlı iletişim formu ve MySQL kaydı.

---

## 0. Sayfalar

| Yol | Ne |
|---|---|
| `/` | Ürünün kendisi. Landing page. |
| `/case` | Ürün kararları. Problem, alınan kararlar ve bedelleri, canlı tasarım sistemi, mimari, ölçülen sonuçlar. |
| `/kvkk` | KVKK aydınlatma metni. Form onayı bu metne verilir. |
| `/gizlilik` | Gizlilik ve çerez politikası. |
| `/yonetim` | Gelen taleplerin listelendiği yönetim paneli (parola korumalı). |
| `/robots.txt` · `/sitemap.xml` | Statik dosya değil, rota: adresler `.env`'deki `APP_URL`'den üretilir. |

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
**genişlik ekseni** ikinci bir ses olarak kullanılır. Üç net karakter:

| Rol | `wdth` | `wght` | Harf aralığı | Satır aralığı | Sınıf |
|---|---|---|---|---|---|
| Başlık | 112 | 730 | −0,032em | 0,98 | `.display` |
| Gövde | 96 | 400 | 0 | 1,68 | `.body-text` |
| Ölçüm okuması | 72 | 650 | +0,005em | — | `.readout` |
| Bölüm etiketi | 104 | 640 | +0,2em | — | `.eyebrow-label` |

Büyük puntoda harf aralığı negatife çekilir, küçük puntoda pozitife: optik
düzeltme. Başlıklar 0,98 satır aralığıyla sıkışır, gövde 1,68 ile nefes alır;
bu zıtlık iki sesi birbirinden ayırır. Bölüm etiketleri dağınık utility
sınıflarından tek bir `.eyebrow-label` bileşenine toplandı.

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
    `consent_at`      DATETIME     NULL DEFAULT NULL,
    `consent_version` VARCHAR(16)  NOT NULL DEFAULT '',
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
- `status` — talebin operasyonel takibi. Panel bu alanı kullanır, şema değişmedi.
- `consent_at` / `consent_version` — KVKK onayının anı ve onaylanan metnin sürümü.
  Mevcut kurulumlar için `../database/migrations/2026_09_16_kvkk_onayi.sql`.
  Bu tarihten önceki kayıtlarda `consent_at` `NULL`'dur ve panel bunu
  "KVKK onayı kaydı yok" uyarısıyla gösterir — geçmişe dönük onay uydurulmaz.

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

```ini
# Yonetim paneli
ADMIN_USER=atolye
ADMIN_PASSWORD_HASH=          # php -r "echo password_hash('parola', PASSWORD_BCRYPT);"

# Yeni talep bildirimi
NOTIFY_TRANSPORT=log          # log | mail
NOTIFY_TO=randevu@ornek.com
NOTIFY_FROM=no-reply@ornek.com
```

`CAMPAIGN_ENDS_AT` boş bırakılırsa geri sayım bölümü hiç render edilmez.
`ADMIN_PASSWORD_HASH` boşsa panel hiçbir parolayı kabul etmez.
`NOTIFY_TO` boşsa bildirim adımı sessizce atlanır.

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
| **İçerik güvenliği (CSP)** | `Core/Security.php` — `script-src` istek başına üretilen **nonce** ile kilitli; sayfadaki tek satır içi blok olan JSON-LD nonce taşır, enjekte edilen bir `<script>` çalışmaz. `frame-ancestors 'none'`, `object-src 'none'`, `form-action 'self'`. |
| **Diğer başlıklar** | `X-Frame-Options: DENY`, `Permissions-Policy` (kamera/mikrofon/konum/ödeme kapalı), HTTPS altında `Strict-Transport-Security`. |
| **Panel oturumu** | Çerez `HttpOnly` + `SameSite=Strict` + yalnızca `/yonetim` yolunda; girişten sonra oturum kimliği yenilenir (session fixation). |
| **CSRF** | Durum değiştiren her panel isteği oturum belirteci taşır, `hash_equals` ile doğrulanır. |
| **Kaba kuvvet** | `Support/LoginThrottle.php` — IP başına 15 dakikada 5 hatalı deneme. Sayaç oturumda değil **dosyada** tutulur; çerez silmek sayacı sıfırlamaz. |
| **Açık yönlendirme** | Panel dönüş adresi yalnızca `/yonetim` ile başlayan göreli yolları kabul eder. |

### CSP'de bilinçli bir taviz

`style-src` içinde `'unsafe-inline'` vardır. Sebebi şudur: HTML'de style attribute'u
yoktur (test bunu doğrular), ancak sürgü konumu ve scroll ilerlemesi sayfaya bir kez
eklenen **boş** bir `<style>` elemanına `insertRule` ile yazılır ve tarayıcı bunu satır
içi stil sayar. Alternatifi constructible stylesheet'tir, eski Safari sürümlerinde
desteklenmez. Taviz yalnızca stile aittir; XSS'in gerçek yüzeyi olan betik tarafı
nonce ile kapalıdır.

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

### Cam yüzeyler videonun üzerinde

Cam paneller `bg-white/[0.09]` ile kurulmuştu; bu, zemini arkadaki videonun
parlaklığına bırakıyordu. Ölçüldü:

| Videonun o bölgedeki parlaklığı | İkincil metin kontrastı |
|---|---|
| Koyu kare (25) | 4,42:1 |
| Orta (60) | 2,72:1 |
| Parlak kare (160) | **1,30:1** |

Yani belirli karelerde yazı pratik olarak görünmez oluyordu. Zemin, yüzey
rengiyle sabitlendi; `backdrop-blur` korunduğu için buzlu cam etkisi kaybolmadı.

Opaklık değeri iki turda belirlendi. İlk turda dört varsayılan parlaklık değerine
karşı hesaplanarak **%86** seçilmişti. İkinci turda ölçüm yöntemi değişti:
değer varsaymak yerine videonun **kendi kareleri** tarandı
(`../tests/hero-kontrast.mjs`, aşağıda). Gerçek karelerle %86, en küçük etiketi
(11 px) **4,49:1**'de bırakıyordu — eşiğin bir yüzde altı. **%90**'a çıkarıldı,
aynı etiket **4,65:1** oldu. Görsel fark gözle ayırt edilmiyor, blur ve kenarlık
aynı; değişen yalnızca panelin videoyu ne kadar geçirdiği.

Aynı tarama ikinci bir kusur buldu: kampanya geri sayımı `.glass-soft`
kullanıyordu (`bg-white/[0.03]`, yani neredeyse saydam) ve videonun üzerinde
duruyordu. Etiketleri **3,74:1**'e kadar düşüyordu. Projenin kendi kuralı bu
durum için zaten yazılıydı — *videonun üzerindeki panel opak yüzey kullanır* —
ama bu panel kuralın dışında kalmıştı. `.glass-strong`'a alındı: **4,95:1**.
Yan fayda: iki hero paneli artık aynı malzemeden görünüyor.

### Erişilebilirlik denetimi

Sayfadaki her metin, gerçek zemin rengine karşı ölçülerek denetlendi.
İlk taramada **33 eleman** WCAG AA eşiğinin altındaydı; iki sistematik sebep vardı:

1. **Marka kırmızısı koyu zeminde metin olarak yetersiz.** `#EA0016` grafit üzerinde
   4,03:1 veriyor. Çözüm renk değiştirmek değil, **ayrı bir metin token'ı** eklemek
   oldu: `--brand-text`. Dolgu, kenarlık ve ikonlar kurumsal `--brand` rengini
   kullanmaya devam ediyor; yalnızca yazı açılmış varyanta geçti.
2. **`--ink-faint` çok koyuydu** (4,11:1) ve 10px etiketlerde kullanılıyordu.
   Açıldı, en küçük etiket 11px'e çıkarıldı.

**Üçüncü tur: "hiçbir yazı sönük görünmesin".** Ölçüm AA eşiğini geçiyordu ama
sayfadaki 12 px soluk gri paragraflar gözle bakınca hâlâ zayıf duruyordu. AA
eşiği okunabilirliğin tabanıdır, hedefi değil. Ev kuralı yükseltildi:

| Metin | Yeni eşik | Nerede ölçülüyor |
|---|---|---|
| Nötr gri | **7:1 (AAA)** | Sayfanın en açık nötr yüzeyinde: panel eylem rozeti (#282A2C) |
| Marka renkli | 4,5:1 (AA) | Aynı yerde |

En açık yüzeye göre ölçmek önemli: aynı token düz zeminde 10,16:1 verirken cam
kartta 8,95:1, rozetin üzerinde 7,77:1 veriyor. En kötü durum ölçülmezse kural
kâğıt üzerinde kalır.

Token değerleri:

| Token | Önce | Sonra | En açık yüzeyde |
|---|---|---|---|
| `--ink-muted` | `139 148 156` | `181 192 203` | 6,04 → **7,77:1** |
| `--ink-faint` | `130 142 152` | `172 185 196` | 5,61 → **7,17:1** |
| `--brand-text` | `255 55 78` | `255 85 105` | 4,65 → **5,32:1** |

Marka kırmızısı neden AAA'ya zorlanmadı: 7:1 verdiği nokta `#FF7384`, yani
somon. Kurumsal kimlik orada biter. Bunun yerine kullanım daraltıldı — marka
rengi yalnızca kısa etiketlerde ve bağlantılarda; hiçbir paragraf marka
renginde değil.

Renk tek başına yetmedi. İçerik taşıyan açıklamalar (temsili görsel ibaresi,
gizlilik cümlesi, yasal notlar) 12 px'lik "ipucu" kademesindeydi. Bunlar ipucu
değil, okunması gereken cümleler: kendi bileşenlerine alındı (`.note`,
`.note-box` — 14 px, gövde tonu, 1,7 satır aralığı). `.field-hint` yalnızca
gerçek mikro ipuçlarında kaldı ve o da gövde tonuna geçti.

Denetçiye de öğretildi: nötr yazı + nötr zemin AAA eşiğiyle, marka renginin
girdiği her yer AA eşiğiyle değerlendiriliyor. Beyaz yazı kurumsal kırmızı
dolgu üzerinde 4,66:1 verir ve bunu yükseltmenin tek yolu kurumsal rengi
değiştirmektir.

**İkinci tur: cam yüzeyler ölçüme dahil edildi.** İlk denetim metinleri düz zemine
(`--surface-900`) karşı ölçüyordu. Oysa kartların çoğu cam yüzey üzerinde duruyor ve
cam zemini açıyor; aynı token orada daha düşük kontrast veriyor. Yeniden ölçüldü:

| Token | Düz zeminde | Cam kart üzerinde | Sonra |
|---|---|---|---|
| `--brand-text` (Bosch) | 5,05:1 | **4,44:1** | 5,24:1 · 4,61:1 |
| `--ink-faint` | 5,12:1 | **4,41:1** | 5,40:1 · 4,70:1 |
| `--brand-text` (Kalibre) | 7,18:1 | 6,32:1 | değişmedi |

İki token birer kademe daha açıldı. Bu, `/case` sayfasındaki karar numaralarını ve
panel bağlantılarını eşiğin üstüne taşıdı; Kalibre teması zaten geçtiği için
dokunulmadı.

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

### Sanat yönetimi: her kırılma noktasına kendi kırpımı

Tam ekrana taşıyınca yeni bir sorun çıktı: 16:9 görsel 2,59 oranındaki bir bandın
içine `object-fit: cover` ile oturunca **%31,5'i kırpılıyordu** ve kesim kötü bir
yerden geçiyordu (duvardaki nesneler yarıdan bölünüyordu).

Çözüm yükseklik sınırı değil, **kaynağı her kırılma noktası için ayrı kırpmak** oldu:

| | Kırpım | Kullanım |
|---|---|---|
| `kaput-*-genis.webp` | 1600×667 (2,40:1) | 640 px üstü, tam ekran bant |
| `kaput-*-dar.webp` | 1341×894 (1,50:1) | 640 px altı |

Kap oranı da o kırpımla eşleşiyor (`aspect-[12/5]` / `aspect-[3/2]`), yani
tarayıcının kırpacağı bir şey kalmıyor:

| | Önce | Sonra |
|---|---|---|
| Kırpılan alan, 1880 px | %31,5 | **%0,4** |
| Kırpılan alan, 390 px | %16 | **%0** |

İki kare de **aynı offset'ten** kesildi; farklı kesilseydi sürgü kaydırıldığında
kareler birbirine oturmaz, karşılaştırma bozulurdu.

Yan fayda: geniş kırpım, atölye diline uymayan arka plan nesnelerini kadraj
dışında bırakıyor.

### Hero yazısının okunabilirliği

Hero'da yazı hareketli video üzerinde durur. Perde (scrim) göz kararı değil,
ölçülerek ayarlanmıştır. Perdeyi bir kademe açmak alt paragrafı eşiğin altına
düşürür. Ayrıca perdeden bağımsız ikinci güvence olarak yazıya ince bir gölge
uygulanmıştır.

**Ölçüm nasıl yapılıyor** (`npm run hero`): hero'daki her yazı görünmez yapılır,
bölüm bir kez fotoğraflanır, sonra fotoğraf tarayıcıya geri verilip her yazının
tam olarak kapladığı dikdörtgenin ortalama rengi okunur. Fotoğraf; video karesi,
perde, cam panel ve gradyanların hepsini birlikte taşır — yani tarayıcının
gerçekten çizdiği şey ölçülür.

CSS katmanlarını hesaplamak burada işe yaramaz, çünkü **perde bir ata elemanın
arka planı değil, ayrı bir örtü katmanıdır**; ata zincirini yürüyen bir hesap onu
hiç görmez ve yazıyı çıplak videoya karşı ölçer. İlk denememde tam bu hataya
düştüm: sonuçlar 1,01:1 gibi imkânsız değerler verdi, çünkü perde hesaba
girmiyordu.

Son ölçüm — 10 kare × 3 ekran genişliği (390 / 1440 / 1920), hero içindeki
**her** metin elemanı:

| | Değer |
|---|---|
| Başlık | 13,95:1 |
| Alt paragraf | 5,81:1 |
| Ölçüm paneli gövdesi | 5,54:1 |
| En küçük etiketler (10–11 px) | 4,65 – 4,95:1 |
| **En kötü eleman** (buton yazısı) | **4,62:1** |
| Eşik altında kalan | **0** |

Önceki sürümde bu bölümde "en kötü 5,2:1" yazıyordu. O değer doğruydu ama
kapsamı dardı: yalnızca başlık ve alt paragraf ölçülmüştü. Her elemanı ölçünce
en kötü değer 4,62'ye iniyor — hâlâ eşiğin üstünde, ama gerçek rakam bu.

Perde 1320px altında yataydan dikeye döner: `.shell` maksimum genişliğine o noktada
ulaşır, altında yazı bloğu tam genişliğe yayıldığı için yatay perde işe yaramaz.

---

## 7. Ekstra modüller ve gerekçeleri

| Modül | Gerekçe |
|---|---|
| **Hero arka plan videosu** | Sessiz, 20 sn dikişsiz döngü (ileri-geri birleştirilmiş). Cam yüzeylerin hareketli görüntü üzerinde durması glassmorphism'in en güçlü göründüğü senaryo. |
| **Scroll ile sürülen paso videosu** | Scroll ilerlemesi videonun zaman çizgisine bağlanır, mikron ve parlaklık sayaçları eşzamanlı sayar. `scroll` dinleyicisi yoktur: IntersectionObserver bölüm görünürken bir rAF döngüsü açar, çıkınca kapatır. Video 3 karede bir keyframe ile kodlanmıştır, seek pürüzsüzdür. Kliple scroll mesafesi birlikte ayarlanır (aşağıya bakın). |
| **Öncesi/sonrası sürgüsü** | Hizmetin çıktısını anlatmak yerine gösterir. `range` input kullanıldığı için klavye ve ekran okuyucu desteği hazır gelir. |
| **Kampanya geri sayımı** | Aciliyet duygusu. Bitiş tarihi `.env`'den; süresi dolunca sayaç gizlenip bilgilendirme mesajına döner. |
| **WhatsApp destek butonu** | Türkiye'de servis randevusu için birincil kanal. Hazır mesaj metniyle açılır. |
| **Referans kartları** | Sosyal kanıt. Yıldız derecelendirmesi ve araç modeli, genel ifadelere göre daha inandırıcı. |
| **Honeypot + oran sınırı** | Bot gönderimlerini CAPTCHA eklemeden azaltır. |
| **Referans numarası** | Başarılı gönderimde `KLB-004271` biçiminde numara döner. Kullanıcıya somut geri bildirim, operasyona takip anahtarı. |
| **`SiteContent` sınıfı** | Tüm metinler tek kaynakta. Şablonlarda dizi tekrarı yok, içerik güncellemesi HTML'e dokunmadan yapılır. |

### Scroll mesafesi klibe göre ayarlanır

Scroll bölümünün yüksekliği keyfi değil; videodaki gerçek hareket miktarına göre
belirlendi. İlk klipte pasta makinesinin yatay konumu kare kare ölçüldü:

| | İlk klip | Kullanılan klip |
|---|---|---|
| Net ilerleme (0-1) | 0,02 | **0,31** |
| Katedilen kare genişliği | %16 | **%34** |
| Çözünürlük | 1280×720 | 1920×1080 kaynak |
| Scroll mesafesi | 190vh'ye indirilmişti | **260vh** |

İlk klipte makine karenin dar bir bandında dönüp başladığı yere dönüyordu; bölüm
"pasoyu izleyin" diyordu ama izlenecek bir ilerleme yoktu, o yüzden scroll
mesafesi kısaltılmıştı. İkinci klip tek yönde ilerlediği için mesafe geri açıldı.

Kodlama ayarı da ölçülerek seçildi: 1600 px çıktı, 1280 px'e göre hiç ek detay
vermiyordu (kenar enerjisi 1,963'e karşı 1,965) ama %46 daha ağırdı.

### Mobil veri koruması

640px altında iki video da **hiç indirilmez**; poster görselleri kalır ve scroll
bölümü sayaç + ışık şeridi efektiyle çalışmaya devam eder. `saveData` açıksa veya
`prefers-reduced-motion` seçiliyse de indirilmez.

Uygulama detayı önemli: video kaynağı HTML'de `src` değil **`data-src`** olarak durur.
`src` ile yazılsaydı tarayıcı sayfayı ayrıştırırken indirmeye başlar, `defer` ile
çalışan JavaScript devreye girdiğinde iş işten geçmiş olurdu. Kaynak yalnızca
koşullar sağlandığında atanır, aksi halde tek bayt inmez.

Ölçülen: mobilde (390px) ilk yükleme **503 KB** ham gövde boyutu, hiç video
isteği yok. PHP'nin dahili sunucusu sıkıştırma yapmıyor; gzip ile aynı sayfa
**~382 KB**'ye iniyor (CSS 77 → 10 KB, HTML 54 → 11 KB). Masaüstünde 7 MB,
videolar poster yüklendikten sonra iniyor, ilk boyamayı etkilemiyor.

### Görünmeyen bir poster her mobil yüklemede 121 KB yiyordu

Süreç fotoğrafları eklendikten sonra sayfa ağırlığını yeniden ölçerken çıktı.
İki ayrı kusur vardı ve ikisi de aynı yerdeydi:

1. **Hero posteri tek kırpımdı.** 1920 piksel genişliğindeki dosya 390 piksellik
   bir telefona da iniyordu — ekranın gösterebileceğinin beş katı veri.
   `<picture>` ile ikinci bir kırpım eklendi: dar ekranda 960 piksellik sürüm,
   124 KB yerine **27 KB**.

2. **`<video poster="...">` niteliği posteri yine de indiriyordu.** `preload="none"`
   olmasına ve mobilde videonun `src`'sinin hiç atanmamasına rağmen tarayıcı
   poster dosyasını çekiyordu. Üstelik o poster hiç görünmüyor: video oynayana
   kadar saydam ve arkasında zaten `<picture>` duruyor. Nitelik kaldırıldı.

| | Önce | Sonra |
|---|---|---|
| Mobil ilk yükleme | 598 KB | **503 KB** |
| İnen poster | 1920px, 124 KB | 960px, 27 KB |
| Görünmeyen dosya için harcanan | 121 KB | 0 |

Ders: "video indirilmiyor" demek yetmiyor, videonun *niteliklerinin* ne
indirdiğini de ölçmek gerekiyor.

---

## 7b. Hizmete açılmak için eklenen katman

Sayfa teknik olarak çalışıyordu ama gerçek bir müşteriyi karşılayamazdı: form
kişisel veri topluyordu ama aydınlatma metni yoktu, talep veritabanına düşüyordu
ama kimse haberdar olmuyordu, `status` alanı şemada duruyordu ama onu görecek bir
arayüz yoktu. Bu bölüm o üç boşluğu kapatır.

### KVKK

| Ne | Nerede |
|---|---|
| Aydınlatma metni | `/kvkk` — Kanun'un 10. maddesindeki başlık sırasıyla: veri sorumlusu, işlenen veriler, amaçlar, hukuki sebep, aktarım, toplama yöntemi, saklama süresi, 11. madde hakları, başvuru yolu. |
| Gizlilik ve çerez politikası | `/gizlilik` |
| Form onayı | Onay kutusu zorunludur; `Validator`'a eklenen `accepted` kuralı işaretsiz gönderimi 422 ile reddeder. |
| Onayın ispatı | Onay anı ve **metnin sürümü** kayıtla birlikte saklanır (`consent_at`, `consent_version`). |

**Sürüm neden kaydediliyor:** metin zamanla değişir. "Onay alındı" demek yetmez;
hangi metne onay verildiği bilinmiyorsa ispat yükümlülüğü karşılanmaz.
`LegalContent::SURUM` değiştiğinde yeni kayıtlar yeni sürümle işaretlenir, eski
kayıtlar eski sürümü taşımaya devam eder. Panelde her kaydın onay sürümü görünür.

**Çerez bildirimi neden yok:** ziyaretçi tarafında çerez oluşturulmadığı için.
Sahte bir onay penceresi koymak yerine gizlilik metninde bunun böyle olduğu yazılı.
Tek istisna panel oturum çerezidir ve o da yalnızca `/yonetim` yolunda oluşur —
`Session.php` cookie yolunu buna göre kısıtlar, yani metindeki ifade kodla korunur.

### Yönetim paneli

`/yonetim` — gelen talepler, duruma göre süzme (`Yeni` / `Okundu` / `Arşiv`),
sayfalama ve tek tıkla durum değiştirme.

- Parola `.env`'de yalnızca **bcrypt özeti** olarak durur, düz metin hiçbir dosyada yok.
- Kullanıcı adı `hash_equals`, parola `password_verify` ile doğrulanır; kullanıcı adı
  yanlış olsa bile `password_verify` çalıştırılır, böylece yanıt süresinden kullanıcı
  adı çıkarılamaz.
- Hata mesajı hangi alanın yanlış olduğunu söylemez (kullanıcı adı sayımını engeller).
- Panel pazarlama düzenini değil kendi düzenini kullanır (`layouts/admin.php`),
  ama **aynı** `app.css` dosyasını okur: `.admin-card` kendi cam kuralını yazmaz,
  `@apply glass` ile türer. Test bunu doğrular.
- `noindex, nofollow` + `robots.txt` içinde `Disallow: /yonetim`.

### Yeni talep bildirimi

`Support/Notifier.php` — taşıyıcı `.env` ile seçilir:

| `NOTIFY_TRANSPORT` | Davranış |
|---|---|
| `log` (varsayılan) | `storage/logs/bildirimler.log` dosyasına yazar. SMTP kurulumu olmayan ortamda akışın çalıştığı doğrulanabilir kalır. |
| `mail` | PHP `mail()` ile gönderir. Konu satırı RFC 2047 base64 ile kodlanır (Türkçe karakter), `Reply-To` müşterinin adresidir. |

Bildirim **kaydın ardından** ve kendi `try/catch`'i içinde çalışır: posta
gönderilemezse talep yine de kaydedilmiştir ve kullanıcı başarı yanıtını alır.
`NOTIFY_TO` boşsa adım sessizce atlanır.

---

## 7c. Sessizce hiçbir şey yapmayan üç sınıf

Referans bölümündeki yıldızlar siyah ve kocaman çiziliyordu. Sebep basitti:
**`.star` sınıfı hiçbir yerde tanımlı değildi.** SVG ne boyut ne dolgu alıyordu,
tarayıcı varsayılanına düşüyordu. Sayfa açıldığından beri böyleydi.

Tanımsız bir sınıf hata vermez, uyarı vermez — sessizce hiçbir şey yapmaz. Bu
yüzden tek tek aramak yerine denetime bir kontrol eklendi: işaretlemede
kullanılan her sınıf, yüklü stylesheet'lerden birinde tanımlı mı?

Kontrol çalışır çalışmaz iki tane daha buldu:

| Sınıf | Ne oluyordu |
|---|---|
| `.star` | Yıldızlar siyah ve varsayılan boyutta |
| `.stat-num` | İstatistik rakamları gövde puntosunda kalıyordu; CSS'te `.fact-num` diye tanımlıydı ama şablon başka ad kullanıyordu |
| `.eyebrow` | Referans bölümünün etiketi düz beyaz; doğrusu `.eyebrow-label` |

`.fact-num` / `.fact-lbl` ise CSS'te tanımlı ama hiç kullanılmayan ölü
kurallardı. Yarım kalmış bir yeniden adlandırmanın iki ucu: şablon bir adı,
stil dosyası başka adı taşıyordu. Şablon `.fact-*` kullanacak şekilde
düzeltildi; hem hata kapandı hem ölü CSS kalktı.

### Denetimin kendisi iki kez yanlış çalıştı

Bu kontrolü yazarken iki tuzağa düştüm ve ikisi de sessiz başarısızlıktı:

1. **`CSSRuleList` yinelenebilir değil.** CSSOM'da `iterable<>` olarak
   tanımlanmamış; `for...of` `TypeError` atıyor. `try/catch` bunu yutunca
   toplayıcı boş kalıyor ve denetim "her sınıf tanımsız" diyordu.

2. **"`cssRules` varsa kapsayıcıdır" varsayımı yanlış.** Chrome, CSS iç içe
   yazım desteğiyle birlikte `CSSStyleRule`'a da `cssRules` verdi (boş liste).
   Önce kapsayıcıyı kontrol edip `continue` eden döngü hiçbir stil kuralının
   `selectorText`'ine ulaşamıyordu.

İkisi de "çalışıyor gibi görünüp hiçbir şey ölçmeyen denetim" üretiyordu — ki
bu, denetim olmamasından daha kötü. Bu yüzden denetim artık **kendini de
kontrol ediyor**: toplayıcı yüzden az sınıf bulursa bunu kusur olarak bildiriyor.

---

## 7d. Yazı zayıf görünüyordu; sebep font değil ağırlıktı

"Yazı karakterleri zayıf geliyor" denince önce fontun yüklenip yüklenmediği
ölçüldü, çünkü en olası açıklama oydu: değişken font yüklenmezse tarayıcı yedek
yazı tipine düşer ve `font-variation-settings` hiçbir işe yaramaz.

Ölçüm bunu eledi. Archivo yüklenmişti (`wght 300–800`, `wdth 62–125`) ve
varyasyon ayarları uygulanıyordu:

```
h2      Archivo  "wdth" 112, "wght" 730
gövde   Archivo  "wdth" 96,  "wght" 400
```

Sorun buradaydı: **gövde 400.** Açık yazı koyu zeminde optik olarak daha ince
görünür — aynı ağırlık açık zeminde daha dolgun okunur. Koyu arayüzler bu yüzden
gövde ağırlığını bir tık yukarı alır.

Aynı cümle canvas'a farklı ağırlıklarda çizilip mürekkep oranı ölçüldü:

| `wght` | Mürekkep oranı |
|---|---|
| 400 | %5,34 |
| 460 | %5,70 |
| 520 | %6,00 |

Fark gerçek ama küçük; asıl karar gözle verildi — aynı paragraf 400/440/480'de
yan yana render edilip karşılaştırıldı.

Sonuç: dağınık sayılar yerine bir **ağırlık ölçeği** kuruldu ve bileşenler o
ölçeği okuyor. Gövde 470, küçük punto 490. Ölçek dışında yalnızca iki sabit
değer kaldı (kart başlığı ve kelime markası), test bunu sayıyor.

Bir şey bilerek değiştirilmedi: `-webkit-font-smoothing: antialiased`. macOS'ta
yazıyı inceltir, yani oradaki inceliği azaltmak için kaldırılabilirdi. Ama koyu
zeminde alt piksel yumuşatması saçaklı görünüyor ve etki platforma bağlı.
Ağırlık ekseni gerçek bir font ekseni olduğu için her yerde aynı sonucu verir;
çözüm oradan yapıldı.

---

## 7e. Çerçeve başlığa değil sonuca

Bölüm başlıklarının düz durduğu, çerçeveye alınabileceği önerildi. Yarısına
katılıp yarısına katılmadım ve sebebini yazmak gerekiyor.

**Neden çerçeve değil:** sayfada zaten çok cam yüzey var — kartlar, paneller,
form, panel listesi. Başlıklar da kutulanırsa neredeyse her şey çerçeveli olur
ve her şey çerçeveliyse hiçbiri önemli görünmez. Kartların "nesne" gibi
okunmasını sağlayan şey, aralarındaki metnin çerçevesiz olması.

**Ama gözlem doğruydu:** başlıklar gerçekten düz duruyordu. Sebep çerçevesizlik
değil, **tekdüzelik**. Sekiz bölümün sekizi de aynı ritimdeydi: etiket, büyük
başlık, paragraf. Aynı desen art arda tekrarlayınca göz kayıyor.

Üç ayrı müdahale yapıldı:

| Ne | Nerede |
|---|---|
| Çizilen aksan çizgisi | Her bölüm başlığının üstünde, bölüm görüşe girince soldan çizilir. Yapı verir, kutuya kapatmaz |
| Satır satır açılış | Başlık, satırları maskenin altından yukarı kayarak gelir |
| Sonuç kutusu | Çerçeve **yalnızca sonuçlara**: sürecin toplam süresi ve öncesi/sonrası ölçümleri |

Sonuç kutusu sayfada iki yerde var ve bu sayı bilinçli; test de bunu
doğruluyor. Çerçevenin anlamı ender olmasından geliyor.

### Satırlara bölmenin iki tuzağı

1. **Alt çıkıntılar kırpılıyordu.** Maske `overflow: hidden`, başlık satır
   yüksekliği 0,98. Türkçede ğ, ç, y, ş bol; pay bırakılmazsa harflerin altı
   kesiliyor. `padding-bottom` + negatif `margin-bottom` çifti yerleşimi
   bozmadan pay açıyor.

2. **Yazı tipi yüklenmeden ölçmek yanlış bölüyor.** Satır kutuları yazı tipine
   bağlı; yedek yazı tipiyle ölçülen satır sonları Archivo yüklenince kayıyor.
   Bölme `document.fonts.ready` beklendikten sonra yapılıyor.

Ayrıca animasyon bitince metin eski haline döndürülüyor. Kalıcı bir DOM
değişikliği bırakmak, yeniden boyutlandırmada satırların yanlış yerde kalmasına
ve metin seçildiğinde parça parça kopyalanmasına yol açardı.

---

## 7f. Tailwind katmanı bir kuralı sessizce yuttu

Görünüme giriş animasyonu ilk yazıldığında `@layer components` içindeydi.
Sayfa açıldı, bölümlerin yarısı görünmedi. Derlenmiş çıktıya bakınca sebep
çıktı:

```css
/* kaynakta yazan */
[data-reveal].is-in { opacity: 1; transform: none; }
[data-reveal-group] > [data-reveal]:nth-child(2) { transition-delay: 80ms; }

/* derlenmiş dosyaya giren */
[data-reveal]:nth-child(2) { transition-delay: 80ms; }
```

Birinci kural tamamen düştü, ikincisinde `[data-reveal-group] >` kırpıldı.
Tailwind katman içindeki kuralları kullanılmayan sınıflara göre budar; bu
seçicilerde Tailwind'in tanıdığı bir sınıf yok, çünkü hepsi **nitelik**
seçicisi. Sonuç: elemanlar `opacity: 0`'da kaldı ve hiçbir şey onları
açmadı.

Kırpılan ikinci kural daha sinsi: `>` birleştiricisi gidince kural her
`[data-reveal]` elemanına uyguluyordu, yani gruba ait olmayan elemanlar da
gecikmeli açılıyordu. Görünürde çalışıyor ama yanlış çalışıyor.

Çözüm katmanın dışına çıkmak oldu. Test de kaynağı değil **derlenmiş
çıktıyı** kontrol ediyor — kural kaynakta doğru ama çıktıda yoksa test
geçerse testin bir değeri kalmaz.

---

## 7g. SEO ve yerel işletme verisi

| Ne | Nerede |
|---|---|
| `canonical` · `og:url` | `.env` içindeki `APP_URL` üzerinden üretilir, sayfaya göre değişir |
| `og:image` · `twitter:card` | Paylaşımda kapak görseli |
| **Schema.org `AutoDetailing`** | Adres, telefon, koordinat ve çalışma saatleri JSON-LD olarak gömülü; Google'da zengin sonuç için |
| Haritada gör | İletişim bölümünde Google Maps bağlantısı |
| **Favicon** | Markaya göre değişen SVG amblem; `Brand.php` içinde tanımlı, tema değişince ikon da değişir |
| **`robots.txt`** | Rota olarak üretilir; sitemap adresi ve `Disallow: /yonetim` içerir |
| **`sitemap.xml`** | Genel sayfalar + yasal metinler; yasal sayfa eklemek sitemap'i kendiliğinden günceller |

`APP_URL` yayına alırken gerçek alan adıyla değiştirilmelidir; canonical ve
og etiketleri otomatik olarak ona göre üretilir.

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

## 8b. Statik dosyalar ve bayt aralığı (Range)

Scroll ile sürülen video bölümü, `video.currentTime` atayarak çalışır. Tarayıcı
bir videoyu ancak sunucu **HTTP Range** isteklerini destekliyorsa "sarılabilir"
sayar; desteklemiyorsa dosyayı tamamen indirse bile `seekable` aralığı boş kalır
ve `currentTime` atamaları **sessizce yok sayılır**.

PHP'nin dahili geliştirme sunucusu Range isteklerine `206` yerine `200` döner.
Sonuç: video ilk karesinde donar, sayaçlar çalışmaya devam ettiği için hata
fark edilmez.

`app/Core/FileServer.php` bunu çözer:

| Durum | Yanıt |
|---|---|
| Aralık istenmemiş | `200` + `Accept-Ranges: bytes` |
| `bytes=0-1023` | `206` + `Content-Range: bytes 0-1023/3200823` |
| `bytes=-500` (son 500 bayt) | `206` |
| Dosya dışı aralık | `416` |
| Dizin dışına çıkma denemesi | `404` (`realpath` ile doğrulanır) |

Ayrıca `ETag` + `304` ve `Cache-Control` üretir.

Bu yüzden `npm run serve` komutu PHP'yi **yönlendirici betiği** ile başlatır
(`php -S ... public/index.php`); aksi halde dahili sunucu statik dosyaları
`index.php`'ye hiç uğratmadan kendi sunar ve sınıf devreye girmez.

Üretimde Apache/nginx statik dosyaları kendi sunar ve Range'i zaten destekler;
bu katman oraya uğramaz.

---

## 9. Testler

```bash
npm test          # 184 birim/duman testi  (php tests/run.php)
npm run yerlesim  # yerlesim denetimi      (6 genislik x 5 sayfa)
npm run kontrast  # kontrast denetimi      (WCAG AA, duz zeminler)
npm run hero      # hero kontrasti         (piksel yontemi, video uzerinde)
npm run denetim   # dordu birden
```

`npm test` harici bağımlılık gerektirmez. Veri erişim katmanı sahte bir PDO ile
test edilir, böylece **MySQL kurulu olmadan da** prepared statement kullanıldığı ve
kullanıcı girdisinin SQL metnine birleştirilmediği doğrulanabilir.

İki denetim betiği (`../tests/yerlesim.mjs`, `../tests/kontrast.mjs`) `playwright-core`
ve yerel bir Chrome ister; ölçüm yapmak için sayfanın gerçekten render edilmesi
gerekir. Sunucu açıkken çalıştırılırlar. Panel denetimi için kimlik bilgisi
ortam değişkeninden verilir:

```bash
PANEL_USER=... PANEL_PASS=... npm run kontrast
```

Mevcut durum: **184 test geçiyor**, yerleşim denetiminde **0 kusur**,
kontrast denetiminde **eşik altı 0 metin**.

| Katman | Test sayısı | Ne doğrulanıyor |
|---|---|---|
| Doğrulama | 13 | Kurallar, kırpma, kontrol karakteri, KVKK onay kuralı |
| Güvenlik | 10 | CSP direktifleri, nonce, başlıklar, HSTS yalnızca HTTPS'te |
| Yasal metinler | 10 | Rota–içerik eşleşmesi, 10. madde başlıkları, tek `h1`, style attribute yok |
| Veri erişimi | 14 | Prepared statement, onay sütunları, panel sorguları, şema dışı durum reddi |
| Giriş freni | 8 | Sayaç, kilit, kilit süresi, IP ayrımı, sıfırlama |
| CSRF ve yönlendirme | 9 | Belirteç üretimi/doğrulaması, açık yönlendirme reddi |
| Range çözümleyici | 8 | Bayt aralığı hesabı ve red koşulları |
| Şablonlar | 21 | Render, style attribute yokluğu, cam sınıfları, panel, onay kutusu |
| Yapılandırma | 4 | `.env` okuyucu, yorum ve tırnak davranışı |
| XSS | 3 | Kaçış fonksiyonu |
| Kurumsal kimlik | 12 | Özgün vektör amblem, palet kodları, marka bağımsızlığı |
| Varlık sürümleme | 4 | Adres damgası, eksik dosya davranışı |
| Okunabilirlik | 12 | Token eşikleri, açıklama metni bileşeni |
| İmleç nişangahı | 9 | Inline stil yokluğu, dokunmatik ve hareket azaltma davranışı |
| Süreç şeridi | 11 | Adım verisi, ray/nokta işaretlemesi, görselsiz hâl |
| Görünüme giriş | 6 | Derlenmiş CSS'te kuralların varlığı, JS-siz davranış |
| Mobil veri | 8 | Tembel yükleme, iki kırpımlı poster, video niteliklerinin indirdiği |
| Başlık ve sonuç | 13 | Çerçevenin yeri, satır bölme, alt çıkıntı payı |
| Bildirim | 1 | Adres tanımsızsa akış sessizce atlanır |

### Rotalar

| Metot | Yol | Denetleyici |
|---|---|---|
| GET | `/` | `HomeController@index` |
| GET | `/case` | `CaseStudyController@index` |
| GET | `/kvkk` · `/gizlilik` | `LegalController@show` |
| GET | `/robots.txt` · `/sitemap.xml` | `SeoController` |
| GET | `/yonetim` | `AdminController@index` |
| GET · POST | `/yonetim/giris` | `AdminController@loginForm` · `@login` |
| POST | `/yonetim/cikis` | `AdminController@logout` |
| POST | `/yonetim/durum` | `AdminController@updateStatus` |
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
| KVKK onayı işaretsiz | 422, yalnızca `consent` alanı işaretlendi, kayıt oluşmadı |
| KVKK onayı işaretli | 200, `consent_at` ve `consent_version` tabloda |
| Yeni talep bildirimi | `storage/logs/bildirimler.log` dosyasına yazıldı |
| Panel girişi, hatalı parola | 303 + "Kullanıcı adı veya parola hatalı", kalan hak 4'e düştü |
| Panel girişi, doğru parola | 303 → `/yonetim`, liste 3 kaydı gösterdi |
| CSP altında sürgü | `--compare-pos` %78'e taşındı, tutamak yerinde |
| CSP altında scroll videosu | `seekable` aralık 0–8 sn, `currentTime` atanabiliyor |
| Yazı tipi yüklemesi | Google Fonts CSP altında yüklendi (`document.fonts.check` doğru) |
| Canlı DOM'da inline stil | 0 |
| Yerleşim denetimi (6 genişlik × 5 sayfa) | 0 kusur: yatay taşma yok, tek `h1`, başlık atlaması yok, alt'sız görsel yok |
| Kontrast denetimi (390 px ve 1440 px, 6 sayfa, ~750 metin) | Eşik altı 0 metin |
| Hero kontrastı (10 kare × 3 genişlik, piksel yöntemi) | En kötü 4,62:1, eşik altı 0 |

Denetimler dört kusur buldu; hepsi düzeltildi:

| Bulgu | Ölçülen | Çözüm | Sonra |
|---|---|---|---|
| Panelde telefon/e-posta bağlantıları, 12 px | 4,44:1 | Renk değil rol değişti: yazı `--ink`, marka rengi alt çizgide | 13,9:1 |
| `/case` karar numaraları ve token etiketleri, cam kart üzerinde | 4,41 – 4,44:1 | `--brand-text` ve `--ink-faint` birer kademe açıldı | 4,6 – 4,7:1 |
| Hero geri sayım paneli, video üzerinde `glass-soft` | 3,74:1 | Panel `.glass-strong`'a alındı (projenin kendi kuralı) | 4,95:1 |
| Hero ölçüm paneli en küçük etiketi, %86 opaklık | 4,49:1 | Panel opaklığı %90'a çıkarıldı | 4,65:1 |

İlk üçü benim eklediğim katmanlarda değil, zaten duran sayfadaydı; denetim
betikleri yazılmadan görünmüyorlardı.

## 11. Yayın öncesi kontrol listesi

Bu proje bir case study olarak teslim ediliyor; aşağıdaki maddeler gerçek bir
alan adına çıkarken yapılması gerekenlerdir.

| # | Yapılacak | Nerede |
|---|---|---|
| 1 | `APP_DEBUG=false`, `APP_ENV=production` | `.env` |
| 2 | `APP_URL` gerçek alan adı (canonical, og, sitemap ondan üretilir) | `.env` |
| 3 | `ADMIN_PASSWORD_HASH` yeni bir parolayla üretilsin | `.env` |
| 4 | `NOTIFY_TRANSPORT=mail` ve çalışan bir gönderici adresi | `.env` |
| 5 | Veri sorumlusu bilgileri: ticaret unvanı, MERSİS, vergi dairesi, KEP | `LegalContent.php` |
| 6 | Öncesi/sonrası kareleri gerçek atölye çekimiyle değişsin | `public/assets/img/` |
| 7 | Web sunucusunun kök dizini `public/` olsun | sunucu yapılandırması |
| 8 | HTTPS açık olsun (HSTS başlığı yalnızca o zaman gönderilir) | sunucu yapılandırması |
| 9 | `storage/` yazılabilir, web'den erişilemez olsun | dosya izinleri |

Maddeler 5 ve 6 gerçek işletme verisi gerektirir; prototipte eksik oldukları
gizlenmiyor, sayfanın üzerinde açıkça yazıyor.

---

## 12. Önceki sürüm

Bu dönüşümden önceki tek dosyalık statik sürüm `_eski/index.static.html`
içinde korunmuştur. Görseller ve videolar `public/assets/` altına taşınmıştır.
