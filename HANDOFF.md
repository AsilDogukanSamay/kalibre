# Devir notu

Bu dosya, projeyi **başka bir yapay zekaya, başka bir sohbete veya başka bir
geliştiriciye** devrederken okutulacak özettir. Tek dosya okutmak, projeyi
sıfırdan anlatmaktan hızlıdır.

## Yeni bir sohbete nasıl devredilir

1. Yeni oturumu **`C:\Users\doguk\Claude Usta`** dizininde aç.
   Dosyaları ayrıca göndermene gerek yok, orada duruyorlar.

   > Depo GitHub'da **`kalibre`** adında; temiz bir klon `kalibre/` klasörü
   > oluşturur. Bu makinedeki klasör tarihsel olarak `kalibre-landing/`
   > adını taşıyor — aşağıdaki yollar ona göre yazılı. Klonla çalışıyorsan
   > `kalibre-landing/` yerine `kalibre/` oku.
2. İlk mesaj olarak şunu yaz:

```
Önce kalibre-landing/HANDOFF.md ve kalibre-landing/README.md dosyalarını oku.
Ayrıntılı gerekçeler kalibre-landing/docs/KARARLAR.md içinde.
Bu bir iş başvurusu case study'si, oradaki kurallara sadık kalman önemli.
Sonra şunu yapmanı istiyorum: ...
```

3. Sunucular bu oturumla birlikte kapanır, yeniden başlatılmaları gerekir:

```bash
npm run db        # veritabani, ayri bir terminalde acik kalmali
npm run start     # CSS derle + sunucu -> http://127.0.0.1:5174
npm test          # 303 test
npm run denetim   # testler + yerlesim + kontrast + hero + hareket denetimi
```

**Önemli:** `npm run serve` PHP'yi yönlendirici betiğiyle başlatır. Elle
`php -S ... -t public` yazarsan Range desteği devre dışı kalır ve scroll
videosu ilk karesinde donar.

---

---

## 1. Bu proje nedir

Bir boya düzeltme / seramik kaplama atölyesi için landing page.
Teknik değerlendirme (iş başvurusu case study) olarak hazırlandı.

| | |
|---|---|
| Frontend | Tailwind CSS 3.4, glassmorphic bileşenler, vanilla JS |
| Backend | PHP 8.4, framework yok, OOP + MVC, kendi PSR-4 autoloader'ı |
| Veritabanı | MySQL / MariaDB, PDO prepared statements |
| Rotalar | `/` landing · `/case` vaka çalışması · `/kvkk` · `/gizlilik` · `/yonetim` panel · `POST /api/contact` |
| Yasal | KVKK aydınlatma metni, gizlilik politikası, formda zorunlu onay (sürümüyle kaydedilir) |

---

## 2. Bilmen gereken on bir kural

Bu projede bilinçli olarak konulmuş, bozulmaması gereken kurallar:

1. **Hiçbir HTML etiketinde `style="..."` olmayacak.** Dinamik değerler bile
   (sürgü konumu, scroll ilerlemesi) inline stil değil, sayfaya bir kez eklenen
   tek kurallık bir stylesheet üzerinden CSS değişkeniyle taşınır.
   `tests/run.php` bunu otomatik doğrular.
2. **Renkler doğrudan yazılmaz.** Tailwind token'ları CSS değişkenlerinden okur
   (`--brand`, `--surface-900`, `--ink` ...). Marka değiştirmek `.env` içinde
   tek satır: `APP_BRAND=bosch` veya `kalibre`.
3. **SQL yalnızca `app/Models/` altında.** Controller ve şablonlarda sorgu yok.
   Tüm sorgular adlandırılmış prepared statement.
4. **Çıktıya giden her dinamik değer `e()` fonksiyonundan geçer.** İstemcide
   `innerHTML` kullanılmaz, `textContent` kullanılır.
5. **Glassmorphism tek kaynaktan türer:** `.glass`, `.glass-strong`,
   `.glass-soft`, `.glass-sheen`, `.glass-card`. Yeni cam yüzey için kural
   yazılmaz, bu sınıflar kullanılır.
6. **Hiçbir yazıyı soldurma.** Nötr gri metin, sayfanın en açık nötr yüzeyinde
   bile 7:1 (AAA) tutmak zorunda. Hiyerarşi punto, ağırlık ve harf aralığıyla
   kurulur. `npm test` token değerlerini hesaplayarak, `npm run kontrast` canlı
   sayfada ölçerek doğrular. Marka renkli yazı bu eşiğe çıkamaz, o yüzden
   yalnızca kısa etiketlerde kullanılır — paragraf marka renginde olmaz.
7. **Ziyaretçi tarafında çerez oluşturma.** Gizlilik metni "bu sitede çerez
   kullanılmıyor" diyor ve bu bir iddia değil, korunması gereken bir durum.
   Oturum çerezi yalnızca `/yonetim` yolunda oluşur (`Session.php` cookie yolunu
   oraya kısıtlar). Analitik veya izleme eklenecekse metin de değişmeli.
8. **Yasal metin değişirse `LegalContent::SURUM` yükselt.** Forma verilen onay,
   onaylanan metnin sürümüyle birlikte kaydedilir. Sürümü yükseltmeden metni
   değiştirmek, eski kayıtların hangi metne onay verdiğini belirsizleştirir.
9. **JavaScript'in taktığı sınıflara bağlı CSS kuralları `@layer` dışında durur.**
   Tailwind, katman içinde seçicide tanıdığı bir sınıf bulamazsa kuralı budar;
   `[data-reveal].is-in` tam olarak böyle kayboldu ve sayfanın yarısı görünmez
   kaldı. Nitelik (attribute) seçicili kurallar `app.css` sonundaki katman dışı
   blokta. Test derlenmiş çıktıyı kontrol ediyor.
10. **`overflow: hidden` bir kaydırma kabı oluşturur.** Scroll'a bağlı
   animasyonlar (`animation-timeline: view()`) görünürlüğü en yakın kaydırma
   kabına göre ölçer; arada `overflow: hidden` olan bir kutu varsa ilerleme
   sabit %50'de donar ve hiçbir şey kımıldamaz. Kırpma gereken yerlerde
   `overflow: clip` kullanılır: aynı görsel sonucu verir, kap oluşturmaz.
   `npm run hareket` bunu ölçerek doğrular — kural dosyada duruyor diye
   çalıştığı anlamına gelmiyor.
11. **Sunucuyu `npm run serve` ile başlat.** PHP'yi yönlendirici betiği olmadan
   çalıştırırsan statik dosyalar `index.php`'ye uğramaz, Range desteği devre
   dışı kalır ve scroll videosu ilk karesinde donar (sayaçlar çalışmaya devam
   ettiği için hata gözden kaçar).

---

## 2b. Commit mesajı biçimi

Depo bir iş başvurusu teslimidir; commit geçmişi de okunur. Biçim:

```
Konu satırı: emir kipi, en fazla 60 karakter, sonunda nokta yok

Gövde neyin neden değiştiğini anlatır ve 72 karakterde sarılır.
Değerlendirme, gerekçe ve ölçüm burada durur — konu satırında değil.

- Çok parçalı değişikliklerde kısa madde listesi
- Ölçüm varsa rakamıyla birlikte
```

Üç kural:

1. **Türkçe karakter kullanılır** (ç, ğ, ı, ö, ş, ü). Git UTF-8 taşır;
   dosyaların geri kalanı düzgün Türkçeyken commit'lerin olmaması
   tutarsızlık yaratır.
2. **Kip tutarlıdır:** emir kipi. "Ekle", "Düzelt", "Kaldır" — "eklendi",
   "düzeltildi" değil.
3. **Konu satırı yorum taşımaz.** "Atölye fotoğrafını kaldır" doğru;
   "sorun yerleşimde değil malzemedeydi" gövdeye ait.

---

## 3. Nerede ne var

```
app/Core/         Çerçeve: Router, Request, Response, Validator, View, Database, Env,
                  Security (CSP + başlıklar), Session (oturum + CSRF),
                  FileServer (Range destekli statik dosya sunucusu)
app/Controllers/  Home, CaseStudy, Contact, Legal (/kvkk, /gizlilik),
                  Seo (robots, sitemap), Admin (/yonetim)
app/Models/       ContactMessage  (tek SQL noktası)
app/Support/      SiteContent (tüm metinler), LegalContent (yasal metinler),
                  Brand (marka teması), CaseStudy, Notifier, LoginThrottle
app/Views/        layouts/{main,admin}.php + partials/*.php + legal.php + admin/*
resources/css/    app.css  ← Tailwind kaynağı, TÜM component sınıfları burada
public/           Web kökü. index.php + assets/{css,js,img,video}
database/         schema.sql + migrations/
storage/          Bildirim günlüğü ve giriş deneme sayacı (versiyonlanmaz)
tests/run.php     Bağımlılıksız duman testleri (303 test)
tests/*.mjs       Yerleşim, kontrast ve hero denetimleri (playwright-core ister)
docs/KARARLAR.md  Ayrıntılı gerekçeler ve ölçümler (README'nin eşlikçisi)
_eski/            Bu dönüşümden önceki tek dosyalık statik sürüm
```

**Metin değiştirmek için** `app/Support/SiteContent.php` yeterli — hizmetler,
süreç adımları, paketler, referanslar, SSS ve iletişim bilgileri oradan gelir.
**İstisna:** bölüm başlıkları ve giriş paragrafları hâlâ kendi şablonlarının
içinde duruyor. Bunları da tek kaynağa taşımak açık bir iyileştirme (§6).
**Stil değiştirmek için** `resources/css/app.css` ve `tailwind.config.js`.

---

## 4. Çalıştırma

```bash
npm install
npm run start        # CSS derle + sunucu -> http://127.0.0.1:5174
npm run dev          # gelistirirken CSS'i izle
npm test             # 303 test
npm run db           # veritabani sunucusu (port 3307)
npm run db:sql       # veritabanina baglan
```

CSS derlenmeden sayfa stilsiz görünür. `public/assets/css/app.css` üretilen
dosyadır, elle düzenlenmez (`.gitignore` içindedir).

### Veritabanı

Bu makinede **taşınabilir MariaDB** kullanıldı; kurulum ve yönetici izni
gerektirmez, `Claude Usta/_db` klasöründe durur, port **3307**.

```bash
npm run db        # sunucuyu baslat, acik kalmali
npm run db:sql    # baglan:  SELECT * FROM contact_messages;
```

Şema bir kez kuruldu. Sıfırdan kurmak gerekirse:
`npm run db:sql < database/schema.sql`

Normal MySQL kuruluysa `.env` içinde `DB_PORT=3306` yap.

### Yönetim paneli

`http://127.0.0.1:5174/yonetim` — kullanıcı adı `.env` içindeki `ADMIN_USER`.
Parolanın kendisi hiçbir dosyada yazılı değildir, yalnızca bcrypt özeti durur.
Parolayı kaybedersen yenisini üret:

```bash
php -r "echo password_hash('yeni-parola', PASSWORD_BCRYPT), PHP_EOL;"
```

Çıkan değeri `.env` içindeki `ADMIN_PASSWORD_HASH` satırına yaz. Beş hatalı
denemeden sonra o IP 15 dakika kilitlenir; kilidi açmak için
`storage/login-denemeleri.json` dosyasını sil.

### Denetim betikleri

`npm run yerlesim` ve `npm run kontrast`, `playwright-core` ile yerel bir Chrome
ister ve **sunucu açıkken** çalışır. `playwright-core` bu projenin kendi
bağımlılığı değildir; üst klasörde bulunduğu için çözülüyor. Başka bir makinede
`npm i -D playwright-core` gerekir. Chrome başka bir yoldaysa:

```bash
CHROME="/yol/chrome.exe" npm run kontrast
```

---

## 5. Doğrulanmış durum

Aşağıdakiler iddia değil, çalıştırılarak ölçüldü:

| Ne | Sonuç |
|---|---|
| Form → gerçek veritabanı kaydı | Çalışıyor, `KLB-000001` referansı döndü |
| SQL injection denemesi | Düz metin olarak kaydedildi, tablo bozulmadı |
| Türkçe karakter | `utf8mb4` ile sorunsuz |
| Oran sınırı | 5. kayıttan sonra 429 |
| Inline stil | Canlı DOM'da 0 |
| Hero yazı kontrastı | En kötü 4,62:1, eşik altı 0 (10 kare × 3 genişlik, her eleman) |
| Mobil veri (390px) | İlk yükleme 503 KB ham / ~382 KB gzip, video isteği 0 |
| KVKK onayı işaretsiz | 422, kayıt oluşmuyor |
| KVKK onayı işaretli | `consent_at` + `consent_version` tabloda |
| Yeni talep bildirimi | `storage/logs/bildirimler.log` dosyasına düştü |
| Panel girişi | Hatalı parolada sayaç düşüyor, doğru parolada liste açılıyor |
| CSP altında sürgü ve scroll videosu | İkisi de çalışıyor, konsolda hata yok |
| Yerleşim (6 genişlik × 5 sayfa) | 0 kusur |
| Kontrast (390 ve 1440 px, panel dahil) | Eşik altı 0 metin |
| Hero kontrastı (piksel yöntemi) | Eşik altı 0 metin |
| Bölüm tonu (3 bant) | Kontrast eşikleri bozulmadı, eşik altı 0 |
| Scroll'a bağlı paralaks | Kart, süreç ve hero: üçü de ölçülerek hareket ediyor |
| Hareket azaltma tercihi | Paralaks tamamen duruyor (8/8) |
| Okuma ilerlemesi | `--okuma-p` scroll ile güncelleniyor, inline stil yok |
| Boya kesiti ölçekli mi | Katman oranları mikron oranlarına birebir eşit (2,182) |
| Atölye fotoğrafı | İki yerleşimde de elendi; kalıntı ve yetim dosya yok |
| Vaka sayfasındaki üç beyan | İkisi kaynağını okuyor, üçüncüsü ölçümle karşılaştırılıyor |
| Kampanya geri sayımı | Geri kondu; boş tarihte render edilmiyor, süresi dolunca bilgilendirmeye dönüyor |
| Temiz klon kurulumu | Boş veritabanına şema, 7 rota 200, form KLB-000001 |
| İkinci marka teması | Ağırlık ölçeği artık kalibre temasında da geçerli |
| Sayfa başlıkları | Dördü de farklı, en uzunu 52 karakter |
| Mobil video | Tam çözünürlük (1280×720), masaüstüyle aynı; scroll pasosu telefonda çalışıyor |
| Testler | 303/303 |

---

## 6. Yapılacaklar

1. **Before/after görselleri yapay zeka üretimi.** Gerçek bir işletmede
   yapılmamış işin öncesi/sonrasını göstermek yanlış beyan olur. Gerçek atölye
   çekimiyle değiştirilmeli. Çekim yaparken tripodu ölçüm başlamadan kur ve iş
   bitene kadar hiç oynatma, yoksa sürgü hizalanmaz.
2. **Bazı teknik değerler varsayım.** Hizmet açıklamalarındaki süreler ve
   garanti rakamları sektörde tipik değerler ama gerçek işletme verisi değil.
3. `.env` içindeki `APP_URL` gerçek alan adıyla değiştirilmeli (canonical ve
   og etiketleri ondan üretilir).
4. **Bildirim `log` modunda.** Gerçek e-posta için `.env` içinde
   `NOTIFY_TRANSPORT=mail` yapmak ve çalışan bir gönderici adresi vermek gerekir.
5. **Veri sorumlusu bilgileri eksik.** Ticaret unvanı, MERSİS, vergi dairesi ve
   KEP adresi `LegalContent.php` içine eklenmelidir. Sayfada bu eksik gizlenmiyor,
   ilgili bölümün altında açıkça yazıyor.
6. Yayın öncesi kontrol listesinin tamamı `README.md` §10'da.

7. **Bölüm başlıkları hâlâ şablonların içinde.** Hizmet, süreç, paket ve SSS
   *içeriği* `SiteContent.php` içinden geliyor ama bölüm başlıkları ve giriş
   paragrafları kendi partial'larında duruyor. "İçerik tek kaynaktan" iddiası
   bu yüzden kısmen doğru. Taşımak mekanik bir iş; başlık metinlerine bağlı
   test yok, yani risksiz.

### Kapanan öneri

Referans kartlarına fotoğraf **eklenmedi ve eklenmeyecek**: referanslar kurgu,
kurgu bir alıntıya kurgu bir yüz eklemek "temsili görsel" çizgisini aşıp
"uydurulmuş kişi"ye geçer. Onun yerine önerilen şey bu turda **yapıldı**: her
referansın altında artık o araca ait ölçüm sonucu var (`152 → 141 µm` gibi) ve
beş yıldız kaldırıldı. Gerekçe `docs/KARARLAR.md` 7j'de.

---

## 6b. Son durum (16 Eylül 2026)

Proje görev tanımındaki (mail) her maddeyi karşılıyor; maddeler canlı sayfada
tek tek ölçülerek doğrulandı, 15/15 geçti. Logo dahil hiçbir açık madde kalmadı.

Son oturumlarda yapılanlar, en yeniden eskiye:

| Ne | Özet |
|---|---|
| **Canlı önizleme** | `asildogukansamay.github.io/kalibre/` — statik kopya, `npm run onizleme:yayinla` ile üretilir (`tools/onizleme.mjs`). Form orada devre dışı, sayfalar `noindex` |
| **Depo adı** | `kalibre-landing` → **`kalibre`**. Bu makinedeki klasör hâlâ eski adı taşıyor; GitHub eski adresi yönlendiriyor |
| **Video kalitesi** | Cihaza göre düşürülmüyor: her yerde 1280×720 kaynak. İki tur küçültme denendi, ikisi de geri alındı (gerekçe KARARLAR 7j) |
| **Scroll videosu** | Sayfa açılırken inmiyor; bölüm bir ekran boyu yaklaşınca iniyor. Masaüstü ilk yükleme ~7 MB → 4.071 KB |
| **Sayfa başlıkları** | `/case` ana sayfayla aynı başlığı taşıyordu; dördü de artık farklı |
| **Temiz klon** | Boş makinede kurulum ölçüldü: 7 rota 200, form kayıt yazıyor, 303 test geçiyor |
| **Denetimler** | İşletim sisteminden bağımsız: Chrome yolu Windows/macOS/Linux'ta aranıyor (`tests/chrome-yolu.mjs`) |
| **Commit biçimi** | §2b'de tanımlı: emir kipi, 60 karakter, Türkçe karakter, konu satırında gerekçe yok |
| **Ritim ve ton** | Sayfa dört tonal bölüme ayrıldı (`--surface-950` eklendi). Ton tek tek bölüme değil **bölüm grubuna** veriliyor; sırayla vermek şerit etkisi yapıyordu |
| **Kırmızı disiplini** | On bölümün etiketi kırmızıydı; üç durağa indi (hero ölçümü, fiyat, randevu). Test en fazla üçe izin veriyor |
| **Geri sayım kaldırıldı** | İndirim sayacı sayfanın ölçüm tonuyla çelişiyordu. Şablon, JS, CSS ve `.env` anahtarı birlikte gitti |
| **Hero ölçüm kartı** | Her satır üç soruyu cevaplıyor: ne ölçüldü, kaç oldu, ne kadar değişti. Delta rakamları iki değerin farkı |
| **Manifesto bölümü** | Kalıba uymayan tek bölüm: etiketi, kartı, ızgarası yok. Altı bölümün aynı iskelette akmasını kırıyor |
| **Referanslar** | Beş yıldız gitti, yerine o araca ait ölçüm geldi. Kart değil, üstten çizgili editoryal sütun |
| **Okuma ilerlemesi** | Menü şeridinin altında, accent renginde, tek kurallık stylesheet üzerinden |
| **Scroll paralaksı** | `animation-timeline: view()`, JavaScript yok. Üç denemede oturdu; `overflow: clip` şart (kural 10) |
| **Hareket denetimi** | `npm run hareket` — animasyonların gerçekten hareket ettiğini, hareket azaltmada durduğunu ölçer |
| **İki sessiz kusur** | Fiyat kartları `.plan` bileşenlerini hiç kullanmıyordu (fiyat ölçüm sesinde değildi); bağlantı stili dört yerde tekrar ediyordu |
| **Beyan kendini doğruluyor** | Vaka sayfasındaki test sayısı artık testin kendi toplamıyla karşılaştırılıyor, elle güncellenmiyor |
| Resmi Bosch logosu | Amblem **ve** kelime markası artık özgün vektör; kaynak dosya depoda, lisansı belgeli |
| Baştan sona geçiş | Bölüm başlıkları tutarlı hale getirildi, etkileşim katmanı (sayaç, akordeon, kart derinliği, buton ışığı), alt bilgi üç sütuna ayrıldı |
| Tanımsız sınıf denetimi | `.star`, `.stat-num`, `.eyebrow` sessizce hiçbir şey yapmıyordu; denetim eklendi, üçü de düzeltildi |
| Ağırlık ölçeği | Koyu zeminde gövde 400 → 470; değerler `--wght-*` token'larında |
| Çerçeve kuralı | Çerçeve başlığa değil **sonuca**; sayfada iki yerde, sayısı testle korunuyor |
| Süreç şeridi | Dört aşama fotoğraflı şeride dönüştü, "elinize geçen" ve toplam süre eklendi |
| Mobil veri | Görünmeyen video posteri her yüklemede 121 KB yiyordu; 598 → 503 KB |

**Bu projede üç kez yanlış alarm yaşandı** ve üçü de ölçülerek elendi:
ekran görüntüsündeki "havada duran + işaretleri" (sabit konumlu nişangahın
Playwright artefaktı), "fotoğrafsız kart" (tembel yükleme) ve "paralaks
çalışmıyor" (ölçüm **gizli** bir tarayıcı panelinde yapılmıştı; `document.hidden`
true iken tarayıcı scroll animasyonlarını askıya alır).

Bir şeyi düzeltmeden önce ölç — ve **ölçüm ortamının kendisini de sorgula.**
Bu kod tabanında bunu yapacak araçlar var: `npm run denetim` beş denetimi
birden çalıştırır.

---

## 7. Marka kullanımı hakkında

Varsayılan tema Bosch kurumsal kimliğini kullanır (gerçek amblem, resmi renkler).
Bu **bağımsız bir prototiptir**, Robert Bosch GmbH ile bağlantısı yoktur ve
sayfa altında bu bilgilendirme görünür. Marka hakları Robert Bosch GmbH'ye aittir.

Kendi markanla kullanmak istersen `.env` içinde `APP_BRAND=kalibre` yeterli;
ya da `app/Support/Brand.php` içine yeni bir dizi ve `resources/css/app.css`
içine üç CSS değişkeni ekleyerek yeni bir tema tanımlayabilirsin.
