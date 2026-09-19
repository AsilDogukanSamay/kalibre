# Glassmorphic Landing Page & İletişim Modülü

İstanbul Maslak'ta boya düzeltme ve seramik kaplama atölyesi için landing page.
Tailwind CSS ile glassmorphic arayüz, saf PHP 8.4 ile OOP/MVC backend,
fetch tabanlı iletişim formu ve MySQL kaydı.

**Kurumsal kimlik:** Bosch Car Service. Özgün vektör amblem, kurumsal renk kodları
ve bu kimliğe göre özelleştirilmiş Tailwind yapılandırması kullanılır.
Bu bağımsız bir prototiptir; marka hakları Robert Bosch GmbH'ye aittir ve sayfa
altında bu bilgilendirme görünür.

| | |
|---|---|
| Frontend | Tailwind CSS 3.4, glassmorphic bileşenler, vanilla JS |
| Backend | PHP 8.4, framework yok, OOP + MVC, kendi PSR-4 autoloader'ı |
| Veritabanı | MySQL / MariaDB, PDO prepared statements |
| Test | 290 test + 4 tarayıcı denetimi, hepsi geçiyor |

> **Ayrıntılı gerekçeler:** [`docs/KARARLAR.md`](docs/KARARLAR.md) — her kararın
> nedeni, denenip bırakılan alternatifler ve ölçüm sonuçları.
> Canlı karşılığı `/case` sayfasıdır.

---

## 1. Kurulum

**Gereken:** PHP **8.1** veya üzeri (proje 8.4 ile geliştirildi; alt sınırı
`Request` sınıfındaki `readonly` özellikler belirliyor), MySQL/MariaDB, Node.js.
Tarayıcı denetimlerini çalıştıracaksanız ayrıca yerel bir Chrome.

```bash
npm install
cp .env.example .env              # DB bilgilerini düzenleyin
mysql -u root -p < database/schema.sql
npm run start                     # CSS derle + sunucu -> http://127.0.0.1:5174
```

Windows, macOS ve Linux'ta çalışır. Chrome'un yeri işletim sistemine göre
otomatik bulunur; bulunamazsa denetim ne yapılacağını söyleyen bir hata verir.

Üretimde web sunucusunun kök dizini **`public/`** olmalıdır.

| Komut | Ne yapar |
|---|---|
| `npm run start` | CSS derler ve sunucuyu başlatır |
| `npm run dev` | Geliştirirken CSS'i izler |
| `npm test` | 290 test. CSS'i önce kendisi derler (`pretest`), çünkü altı test derlenmiş çıktıyı okur |
| `npm run denetim` | Beşi birden: testler + yerleşim + kontrast + hero piksel kontrastı + hareket |

Tarayıcı denetimleri (`yerlesim`, `kontrast`, `hero`, `hareket`) `playwright-core`
ile **yerel bir Chrome** açar; `npm install` bunu kurar ama Chrome'un kendisi sizde
kurulu olmalı. Başka bir yoldaysa:

```bash
CHROME="/yol/chrome.exe" npm run denetim
```

Denetimler **sunucu açıkken** çalışır (`npm run start` ayrı bir terminalde).

> `package.json` içindeki `db` ve `db:sql` betikleri yalnızca geliştirme
> makinesine özeldir: depo dışındaki taşınabilir bir MariaDB kurulumunu
> başlatırlar. Projeyi incelemek için bunlara ihtiyacınız yok, kendi MySQL
> kurulumunuzu kullanın; şema `database/schema.sql` içinde. Farklı port
> kullanıyorsanız `.env` içinde `DB_PORT` satırını düzenleyin.

Yerleşim denetimi **tanımsız sınıf** da arar: işaretlemede kullanılıp hiçbir stylesheet'te karşılığı olmayan sınıf sessizce hiçbir şey yapmaz. Üç tane buldu; ayrıntı [`docs/KARARLAR.md`](docs/KARARLAR.md).

> `npm run serve` PHP'yi **yönlendirici betiğiyle** başlatır. Elle
> `php -S ... -t public` yazılırsa HTTP Range desteği devre dışı kalır ve scroll
> videosu ilk karesinde donar. Gerekçe: [`docs/KARARLAR.md`](docs/KARARLAR.md#8b-statik-dosyalar-ve-bayt-aralığı-range).

---

## 2. Sayfalar

| Yol | Ne |
|---|---|
| `/` | Landing page |
| `/case` | Ürün kararları: problem, alınan kararlar ve bedelleri, canlı tasarım sistemi, ölçülen sonuçlar |
| `/kvkk` · `/gizlilik` | Aydınlatma metni ve gizlilik politikası |
| `/yonetim` | Gelen taleplerin yönetim paneli (parola korumalı) |
| `/robots.txt` · `/sitemap.xml` | Rota olarak üretilir; adresler `.env`'deki `APP_URL`'den gelir |

---

## 3. Mimari

Framework kullanılmadı; **saf PHP 8.4 ile OOP + MVC** katmanlı yapı.
Composer bağımlılığı yok, PSR-4 uyumlu kendi autoloader'ı var.
Spagetti PHP yoktur: hiçbir dosyada HTML ile sorgu iç içe geçmez.

```
public/index.php  →  Router  →  Controller  →  Validator / Model  →  Response | View
```

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
│   │   ├── View.php         şablon motoru + XSS kaçışı
│   │   ├── Security.php     CSP ve güvenlik başlıkları
│   │   ├── Session.php      panel oturumu + CSRF
│   │   └── FileServer.php   Range destekli statik dosya sunucusu
│   ├── Controllers/         Home, CaseStudy, Contact, Legal, Seo, Admin
│   ├── Models/              ContactMessage  ← tek SQL noktası
│   ├── Support/             SiteContent, LegalContent, Brand, CaseStudy,
│   │                        Notifier, LoginThrottle
│   └── Views/               layouts, partials, errors
├── resources/css/app.css    ← Tailwind kaynağı + component katmanı
├── tailwind.config.js       ← kurumsal kimlik yapılandırması
├── database/                schema.sql + migrations/
├── tests/                   run.php (PHP) + *.mjs (tarayıcı denetimleri)
└── docs/KARARLAR.md         ← ayrıntılı gerekçeler ve ölçümler
```

**Katman sorumlulukları:** doğrulama `Validator`'da, SQL yalnızca `Models/` altında,
çıktı kaçışı yalnızca `View`'da, yapılandırma yalnızca `Env`'de.

**DRY:** tüm metinler `SiteContent`, yasal metinler `LegalContent`, marka teması
`Brand` sınıfında tek kaynaktan gelir. Cam yüzeyler beş sınıftan türer
(`glass`, `glass-strong`, `glass-soft`, `glass-sheen`, `glass-card`); yeni bir cam
yüzey için kural yazılmaz, sınıf kullanılır.

---

## 4. Kurumsal kimlik

**Amblem ve kelime markası özgün vektördür.** İkisi de Bosch'un kendi marka
rehberinden (`brandguide.bosch.com`) çıkan resmi dosyadan alınmıştır; dosyanın
kendisi `public/assets/img/bosch-logo-kaynak.svg` içinde saklanır.
Kaynak: Wikimedia Commons, *File:Bosch-logo.svg* — eşik altı olduğu için kamu
malı (PD-textlogo), ayrıca ticari marka bildirimi taşır.

Renk `currentColor` ile tasarım sisteminden gelir: koyu zeminde markanın negatif
kullanımına uygun olarak yazı açık tonda basılır, kurumsal kırmızı amblemde
yaşar. Aynı resmi amblem favicon'da da kullanılır.

"Car Service" alt tanımlayıcısı resmi dosyada yoktur; tipografiktir ve bu
şablonda açıkça yazılıdır. Bosch Sans lisanslı olduğu için ikinci bir yazı tipi
projeye dahil edilmemiştir.

Testler bunu path verisinin ayırt edici bir parçasını arayarak doğrular —
"benzerini çizip koymak" testi geçiremez.

**Kurumsal palet — üç rol, üçü de kullanılıyor:**

| Rol | Kod | Token | Nerede |
|---|---|---|---|
| primary | `#EA0016` | `brand` | Buton, vurgu, ikon, kenarlık |
| hover | `#B8000F` | `brand-dark` | Buton hover |
| secondary | `#00509D` | `brand-secondary` | Ölçüm göstergesinin kanalı |
| accent | `#008ECF` | `brand-accent` | Canlı ölçüm değeri, ışık şeridi |
| metin | `#FF5569` | `brand-text` | Koyu zeminde marka renkli **yazı** (kısa etiketler) |

Kırmızı marka ve eylem rengidir; mavi tonlar ölçüm dilidir. Ayrı bir `brand-text`
token'ı vardır çünkü `#EA0016` koyu zeminde metin olarak 4,03:1 verir, WCAG AA
eşiği 4,5. Dolgu ve ikonlar kurumsal rengi kullanmaya devam eder; yalnızca yazı
açılmış varyanta geçer.

### Okunabilirlik kuralı: hiçbir yazı sönük değil

Sayfada gri bir metnin "geri planda" olması soldurularak değil, punto, ağırlık
ve harf aralığıyla anlatılır. Somut kural:

| Metin | Eşik | Nerede ölçülüyor |
|---|---|---|
| Nötr gri yazı | **7:1 (WCAG AAA)** | Sayfanın **en açık** nötr yüzeyinde — panel eylem rozeti |
| Marka renkli yazı | 4,5:1 (AA) | Aynı yerde |

Marka renkli yazı neden AAA'ya zorlanmıyor: kurumsal kırmızının 7:1 verdiği
nokta `#FF7384`, yani somon. Kimlik orada biter. Bu yüzden marka rengi yalnızca
kısa etiketlerde ve bağlantılarda kullanılır — **hiçbir paragraf marka renginde
değildir**. Beyaz yazının kurumsal kırmızı dolgu üzerindeki değeri de 4,66:1'dir
ve bunu yükseltmenin tek yolu kurumsal rengi değiştirmek olurdu.

Bu kural `npm test` içinde (token değerleri app.css'ten okunup hesaplanır) ve
`npm run kontrast` içinde (canlı sayfada ölçülür) ayrı ayrı doğrulanır.

**Tasarım sistemi marka bağımsızdır.** Tailwind token'ları CSS değişkenlerini
okur, değişkenler `:root[data-brand="..."]` ile değişir. Marka değiştirmek tek bir
`.env` satırıdır (`APP_BRAND=bosch | kalibre`); hiçbir sınıf, şablon veya bileşen
değişmez. Test bunu doğrular: marka adı şablonlara gömülü değildir.

**Tipografi:** tek aile (Archivo variable). İkinci bir yazı tipi yerine genişlik
ekseni ikinci ses olarak kullanılır — başlık `wdth 112`, gövde `96`, ölçüm
okuması `72`.

**Ağırlık ölçeği koyu zemine göre kurulur.** Açık yazı koyu zeminde optik olarak
daha ince görünür; aynı ağırlık açık zeminde daha dolgun okunur. Bu yüzden gövde
400 değil **470**, küçük punto **490**. Değerler `--wght-*` token'larında tek
yerde durur; bir bileşen ince kaldığında elle ayarlanmaz, ölçek kaydırılır.

| Kademe | Değer | Nerede |
|---|---|---|
| `--wght-body` | 470 | Gövde metni, paragraflar |
| `--wght-body-sm` | 490 | Küçük punto — daha çok ağırlık ister |
| `--wght-medium` | 600 | Butonlar, form etiketleri, SSS başlıkları |
| `--wght-strong` | 660 | Kart başlıkları |
| `--wght-display` | 730 | Bölüm başlıkları |

Ölçek dışında yalnızca iki sabit değer var ve ikisi de bilinçli: kart başlığı
(680, ara kademe) ve kelime markası (800, logo kilidinin parçası). Test bunu
sayıyor.

---

## 5. Veritabanı şeması

```sql
CREATE TABLE `contact_messages` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`  VARCHAR(120)  NOT NULL,
    `email`      VARCHAR(180)  NOT NULL,
    `phone`      VARCHAR(32)   NOT NULL,
    `message`    TEXT          NOT NULL,
    `ip_address` VARCHAR(45)   NOT NULL DEFAULT '',   -- IPv6 icin 45 karakter
    `user_agent` VARCHAR(255)  NOT NULL DEFAULT '',
    `status`     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    `consent_at`      DATETIME     NULL DEFAULT NULL,
    `consent_version` VARCHAR(16)  NOT NULL DEFAULT '',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_email`      (`email`),
    KEY `idx_ip_created` (`ip_address`, `created_at`),   -- spam freni sorgusu
    KEY `idx_status`     (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- `utf8mb4` — Türkçe karakter güvenliği.
- `idx_ip_created` — spam freni sorgusu (ip + zaman aralığı) tam bu indeksi kullanır.
- `status` — talebin operasyonel takibi; yönetim paneli bu alanı kullanır.
- `consent_at` / `consent_version` — KVKK onayının anı ve onaylanan metnin sürümü.
  Mevcut kurulumlar için `database/migrations/2026_09_16_kvkk_onayi.sql`.

---

## 6. .env yapılandırması

Hassas bilgi kod tabanında tutulmaz. `.env` `.gitignore` içindedir,
`.env.example` şablon olarak versiyonlanır.

```ini
APP_NAME="Kalibre"
APP_URL=http://127.0.0.1:5174   # yayinda kendi alan adiniz
APP_ENV=local                   # yayinda: production
APP_DEBUG=true                  # yayinda MUTLAKA false
APP_TIMEZONE=Europe/Istanbul
APP_BRAND=bosch                 # bosch | kalibre

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kalibre
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

CONTACT_RATE_LIMIT=5            # spam freni: pencere basina gonderim
CONTACT_RATE_WINDOW_MINUTES=10


ADMIN_USER=atolye               # yonetim paneli
CAMPAIGN_ENDS_AT=2027-01-03T23:59:59+03:00   # bos ise geri sayim render edilmez
ADMIN_PASSWORD_HASH=            # php -r "echo password_hash('parola', PASSWORD_BCRYPT);"

NOTIFY_TRANSPORT=log            # log | mail
NOTIFY_TO=randevu@ornek.com
NOTIFY_FROM=no-reply@ornek.com
```

`APP_URL` canonical, og etiketleri ve sitemap'in kaynağıdır.
`ADMIN_PASSWORD_HASH` boşsa panel hiçbir parolayı kabul etmez.
`NOTIFY_TO` boşsa bildirim adımı sessizce atlanır.

---

## 7. Güvenlik

| Önlem | Nerede |
|---|---|
| **SQL Injection** | `Models/ContactMessage.php` — tüm sorgular PDO named prepared statement, `ATTR_EMULATE_PREPARES => false` ile gerçek sunucu tarafı hazırlama |
| **XSS** | `View::e()` — `htmlspecialchars` + `ENT_QUOTES` + `ENT_SUBSTITUTE`. İstemcide `innerHTML` değil `textContent` kullanılır |
| **Validation** | `Core/Validator.php` — `required`, `email`, `phone`, `min`, `max`, `accepted`. Kontrol karakterleri temizlenir |
| **Hata sızıntısı** | PDO istisnası yakalanır, kullanıcıya genel mesaj döner, teknik detay `error_log`'a yazılır |
| **Spam** | Bal küpü (honeypot) + IP bazlı oran sınırı |
| **CSP** | `Core/Security.php` — `script-src` istek başına üretilen nonce ile kilitli, `frame-ancestors 'none'`, `object-src 'none'`, `form-action 'self'` |
| **Diğer başlıklar** | `nosniff`, `Referrer-Policy`, `X-Frame-Options: DENY`, `Permissions-Policy`, HTTPS altında HSTS |
| **Panel** | bcrypt parola özeti, oturum yenileme, CSRF belirteci, IP başına giriş freni, açık yönlendirme koruması |
| **Dizin güvenliği** | Web kökü `public/`. `app/`, `.env`, `database/`, `storage/` web'den erişilemez |

CSP'de `style-src 'unsafe-inline'` bilinçli ve dar bir tavizdir; gerekçesi
[`docs/KARARLAR.md`](docs/KARARLAR.md) içinde.

---

## 8. Ekstra modüller ve gerekçeleri

| Modül | Gerekçe |
|---|---|
| **Hero arka plan videosu** | Cam yüzeylerin hareketli görüntü üzerinde durması, glassmorphism'in en güçlü göründüğü senaryo |
| **Scroll ile sürülen paso videosu** | Scroll ilerlemesi videonun zaman çizgisine bağlanır, mikron ve parlaklık sayaçları eşzamanlı sayar. `scroll` dinleyicisi yok: IntersectionObserver bölüm görünürken rAF döngüsü açar |
| **Öncesi/sonrası sürgüsü** | Hizmetin çıktısını anlatmak yerine gösterir. `range` input kullanıldığı için klavye ve ekran okuyucu desteği hazır gelir |
| **Kampanya geri sayımı** | Aciliyet. Bitiş tarihi `.env`'den gelir; boş bırakılırsa bölüm hiç render edilmez, süresi dolunca sayaç gizlenip bilgilendirmeye döner |
| **Okuma ilerlemesi** | Menü şeridinin altındaki 1 piksellik çizgi okunan mesafeyi gösterir. Sayfanın dili ölçüm; rengi bu yüzden accent (canlı ölçüm değeri), marka kırmızısı değil. Değer tek kurallık bir stylesheet üzerinden taşınır, `style` attribute'u yazılmaz |
| **Scroll'a bağlı paralaks** | Fotoğraflar kendi kutularının içinde scroll ile kayar, hero videosu yavaşça yaklaşır. **JavaScript yok:** `animation-timeline: view()`. `@supports` içinde durduğu için desteklemeyen tarayıcıda hiçbir şey eksilmez |
| **Manifesto bölümü** | Sayfanın kalıbına uymayan tek bölüm: etiketi, kartı ve ızgarası yok. Altı bölümün aynı iskelette akmasını kıran editoryal duraklama |
| **Boya kesiti** | Manifesto cümlesinin kanıtı; fotoğraf değil. Katman yükseklikleri mikron değerlerinin kendisi (48/22/38/30 = 138), güvenli sınırın konumu 30/48 oranı. Ölçek testle korunuyor |
| **WhatsApp destek butonu** | Türkiye'de servis randevusu için birincil kanal. Hazır mesaj metniyle açılır |
| **Referans kartları** | Sosyal kanıt. Yıldız derecelendirmesi kaldırıldı: her sitede aynı duruyor ve hiçbir şey ölçmüyor. Yerine her referansın altında o araca ait ölçüm sonucu var |
| **Referans numarası** | Başarılı gönderimde `KLB-004271` biçiminde numara. Kullanıcıya somut geri bildirim, operasyona takip anahtarı |
| **KVKK katmanı** | Kişisel veri toplayan form aydınlatma metni olmadan yayına çıkamaz. Onayın anı **ve metnin sürümü** kayıtla saklanır; metin değişince hangi kaydın neye onay verdiği belli kalır |
| **Yönetim paneli** | Talep veritabanına yazılıyordu ama kimse haberdar olmuyordu. `status` alanı şemada zaten hazırdı, arayüzü yoktu |
| **Bildirim katmanı** | Yeni talep atölyeye düşer. `log` modunda dosyaya yazar (SMTP yokken de akış doğrulanabilir), `mail` modunda gönderir |
| **Mobil veri koruması** | 640px altında iki video da **hiç** indirilmez. Kaynak `data-src` ile tutulur; `src` yazılsaydı tarayıcı ayrıştırma sırasında indirmeye başlardı. Hero posteri iki kırpımda: dar ekrana 960px'lik sürüm iner. Ölçülen: 390px'te ilk yükleme **503 KB** ham (gzip ile ~382 KB), video isteği 0 |
| **Range destekli dosya sunucusu** | `video.currentTime` ancak sunucu HTTP Range desteklerse çalışır. PHP'nin dahili sunucusu `206` yerine `200` döner; `Core/FileServer.php` bunu çözer |
| **Varlık sürümleme** | `asset()` dosyanın değişme zamanını adrese ekler (`app.css?v=6aaa55f3`). Sürümlü adres bir yıl + `immutable`, sürümsüz adres bir saat önbelleklenir. Yayına alınan yeni CSS geri dönen ziyaretçiye anında ulaşır; "sürüm atlamayı unutma" diye bir adım kalmaz |
| **Süreç şeridi** | Kartlar yan yana duruyordu ama aralarındaki sıra görünmüyordu. Artık bir şeride diziliyorlar: her adımın noktası, noktalar arasında görüşe girince dolan bir çizgi. Her adım "elinize geçen" bilgisini de taşıyor ve bölümün sonunda toplam süre duruyor — müşterinin ilk sorduğu şey aracın kaç gün atölyede kalacağı |
| **Sonuç kutusu** | Çerçeve sayfada **ender** kullanılır ve yalnızca bir sonucu sarar: ölçülmüş bir değer ya da bir taahhüt. Başlıklara uygulanmaz — her şey çerçeveliyse hiçbiri önemli görünmez. İki yerde var: sürecin toplam süresi ve öncesi/sonrası ölçümleri |
| **Satır satır başlık açılışı** | Başlık, satırları maskenin altından yukarı kayarak geliyor. Satırlara bölme `Range` API ile yapılır — tarayıcının gerçekte nereye sardığı ölçülür, genişlik tahmin edilmez. `innerHTML` kullanılmaz. Animasyon bitince metin eski haline döner, kalıcı DOM değişikliği bırakmaz |
| **Ölçüm sayaçları** | İstatistikler görüşe girince sıfırdan hedefe sayar. Sayfanın dili ölçüm olduğu için dekoratif değil: rakam "yazılmış" değil "okunmuş" gibi geliyor. Türkçe binlik ayracı korunur |
| **SSS akordeonu** | `<details>` kapalıyken içeriği hiç render etmediği için saf CSS geçişi çalışmaz; açılma/kapanma yönetilir. Yükseklik inline stille değil, her panel için eklenen tek kurallık stylesheet üzerinden taşınır. Geçiş bitmezse zaman aşımı devreye girer |
| **Görünüme giriş** | Bölümler ve kartlar görüşe girince bir kez açılıyor, grup içinde sırayla. Gecikme JavaScript'ten inline stille değil, sıraya göre CSS'te tanımlı. Tek seferlik: açılan eleman gözlemden çıkarılıyor |
| **Ölçüm nişangahı (imleç)** | Jenerik bir takip noktası değil: ince halka + artı biçiminde iki tik, parlaklık ölçerin nişangahı. Tıklanabilir hedefte halka açılır, tikler çekilir. Yerli imleç gizlenmez. Konum inline stille değil, sürgüyle aynı CSS-değişkeni yöntemiyle taşınır. Dokunmatik ekranda ve `prefers-reduced-motion` tercihinde hiç çalışmaz; boşta `requestAnimationFrame` döngüsü kapanır |
| **SEO** | Schema.org `AutoDetailing` JSON-LD, canonical/og etiketleri, favicon, `robots.txt`, `sitemap.xml` |

---

## 8b. Görsel üretimi

Sayfadaki altı görsel slotu (hero posteri, bento kartları, süreç şeridi)
temsilidir ve yapay zekâ ile üretilmiştir. Rastgele üretilmediler: önce ortak
bir ışık dili tanımlandı (tek soğuk mavi inceleme lambası, doygunluğu alınmış
grafit ve kirli beyaz, sıcak turuncu yok), her slot promptu o bloğa bağlandı.
Sayfanın görsel bütünlüğü buradan geliyor.

Kullanılan promptların tamamı `docs/GORSEL-PROMPTLARI.md` içinde. Yayına
alınırken bu kareler atölyede çekilmiş gerçek karelerle değiştirilmelidir;
sayfa bunu kendi içinde de açıkça yazıyor (öncesi/sonrası ve referans
bölümlerinin altındaki bilgilendirmeler).

---

## 9. Testler ve denetimler

```bash
npm test          # 290 test (php tests/run.php) - harici bagimlilik yok
npm run yerlesim  # 6 genislik x 5 sayfa: yatay tasma, h1, baslik atlamasi, alt metni
npm run kontrast  # WCAG AA, duz zeminler ve cam yuzeyler
npm run hero      # piksel kontrasti: yazinin foto/video uzerinde durdugu sahneler
npm run hareket   # scroll'a bagli animasyonlar gercekten hareket ediyor mu
npm run denetim   # besi birden
```

Veri erişim katmanı sahte bir PDO ile test edilir; **MySQL kurulu olmadan da**
prepared statement kullanıldığı ve kullanıcı girdisinin SQL metnine
birleştirilmediği doğrulanabilir. `.mjs` denetimleri `playwright-core` ve yerel
bir Chrome ister, sunucu açıkken çalışır.

**Mevcut durum:** 290 test geçiyor · yerleşim 0 kusur · kontrast eşik altı 0
(panel dahil) · hero eşik altı 0 · hareket 8/8.

`npm run hareket` sayfanın **görünür** olmasını gerektirir: `document.hidden`
true iken tarayıcı scroll'a bağlı animasyonları askıya alır ve her değer
hareketsiz okunur. Bu bir kez yanlış alarma yol açtı, gerekçesi
`docs/KARARLAR.md` 7j'de.

### API sözleşmesi

`POST /api/contact` — `Content-Type: application/json`

| Durum | HTTP | Gövde |
|---|---|---|
| Başarılı | 200 | `{"ok":true,"message":"...","reference":"KLB-004271"}` |
| Doğrulama hatası | 422 | `{"ok":false,"message":"...","errors":{"email":"..."}}` |
| Oran sınırı | 429 | `{"ok":false,"message":"...","errors":[]}` |
| Veritabanı erişilemez | 503 | `{"ok":false,"message":"...","errors":[]}` |

### Uçtan uca doğrulanan senaryolar

Gerçek bir veritabanına karşı çalıştırıldı (MariaDB 11.4, PDO `mysql`):
form gönderimi ve kayıt · Türkçe karakter · SQL injection denemesi (düz metin
olarak kaydedildi, tablo bozulmadı) · boş form · geçersiz e-posta · honeypot ·
oran sınırı · veritabanı kapalı · KVKK onayı olan ve olmayan gönderim ·
panel girişi. Tam liste ve ölçümler: [`docs/KARARLAR.md`](docs/KARARLAR.md).

---

## 10. Yayın öncesi kontrol listesi

Bu proje bir case study olarak teslim ediliyor; aşağıdakiler gerçek bir alan
adına çıkarken yapılması gerekenlerdir.

| # | Yapılacak | Nerede |
|---|---|---|
| 1 | `APP_DEBUG=false`, `APP_ENV=production` | `.env` |
| 2 | `APP_URL` gerçek alan adı | `.env` |
| 3 | `ADMIN_PASSWORD_HASH` yeni parolayla üretilsin | `.env` |
| 4 | `NOTIFY_TRANSPORT=mail` ve çalışan gönderici adresi | `.env` |
| 5 | Veri sorumlusu bilgileri: ticaret unvanı, MERSİS, KEP | `LegalContent.php` |
| 6 | Öncesi/sonrası kareleri gerçek atölye çekimiyle değişsin | `public/assets/img/` |
| 7 | Web sunucusu kökü `public/`, HTTPS açık | sunucu yapılandırması |

Maddeler 5 ve 6 gerçek işletme verisi gerektirir. Prototipte eksik oldukları
gizlenmiyor; öncesi/sonrası görsellerinin temsili olduğu sayfanın üzerinde yazıyor.

---

## 11. Marka kullanımı

Varsayılan tema Bosch Car Service kurumsal kimliğini kullanır. Bu **bağımsız bir
prototiptir**, Robert Bosch GmbH ile ticari veya kurumsal bağlantısı yoktur;
marka adı, amblem ve renkler yalnızca kurumsal kimliğe sadık arayüz tasarımını
göstermek amacıyla kullanılmıştır. Marka hakları Robert Bosch GmbH'ye aittir ve
sayfa altında bu bilgilendirme görünür.

`APP_BRAND=kalibre` ile proje kendi markasıyla da çalışır.

---

Bu dönüşümden önceki tek dosyalık statik sürüm `_eski/index.static.html`
içinde korunmuştur.
