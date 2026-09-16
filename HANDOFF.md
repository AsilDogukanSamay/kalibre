# Devir notu

Bu dosya, projeyi **başka bir yapay zekaya, başka bir sohbete veya başka bir
geliştiriciye** devrederken okutulacak özettir. Tek dosya okutmak, projeyi
sıfırdan anlatmaktan hızlıdır.

## Yeni bir sohbete nasıl devredilir

1. Yeni oturumu **`C:\Users\doguk\Claude Usta`** dizininde aç.
   Dosyaları ayrıca göndermene gerek yok, orada duruyorlar.
2. İlk mesaj olarak şunu yaz:

```
Önce kalibre-landing/HANDOFF.md ve kalibre-landing/README.md dosyalarını oku.
Bu bir iş başvurusu case study'si, oradaki kurallara sadık kalman önemli.
Sonra şunu yapmanı istiyorum: ...
```

3. Sunucular bu oturumla birlikte kapanır, yeniden başlatılmaları gerekir:

```bash
npm run db        # veritabani, ayri bir terminalde acik kalmali
npm run start     # CSS derle + sunucu -> http://127.0.0.1:5174
npm test          # 34 test
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
| Rotalar | `/` landing · `/case` vaka çalışması · `POST /api/contact` |

---

## 2. Bilmen gereken beş kural

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
6. **Sunucuyu `npm run serve` ile başlat.** PHP'yi yönlendirici betiği olmadan
   çalıştırırsan statik dosyalar `index.php`'ye uğramaz, Range desteği devre
   dışı kalır ve scroll videosu ilk karesinde donar (sayaçlar çalışmaya devam
   ettiği için hata gözden kaçar).

---

## 3. Nerede ne var

```
app/Core/         Çerçeve: Router, Request, Response, Validator, View, Database, Env,
                  FileServer (Range destekli statik dosya sunucusu)
app/Controllers/  HomeController, CaseStudyController, ContactController
app/Models/       ContactMessage  (tek SQL noktası)
app/Support/      SiteContent (tüm metinler), Brand (marka teması), CaseStudy
app/Views/        layouts/main.php + partials/*.php
resources/css/    app.css  ← Tailwind kaynağı, TÜM component sınıfları burada
public/           Web kökü. index.php + assets/{css,js,img,video}
database/         schema.sql
tests/run.php     Bağımlılıksız duman testleri (34 test)
_eski/            Bu dönüşümden önceki tek dosyalık statik sürüm
```

**Metin değiştirmek için** `app/Support/SiteContent.php` yeterli, HTML'e dokunma.
**Stil değiştirmek için** `resources/css/app.css` ve `tailwind.config.js`.

---

## 4. Çalıştırma

```bash
npm install
npm run start        # CSS derle + sunucu -> http://127.0.0.1:5174
npm run dev          # gelistirirken CSS'i izle
npm test             # 34 test
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
| Hero yazı kontrastı | En kötü 5,2:1 (WCAG AA eşiği 4,5) |
| Mobil veri (375px) | 393 KB, hiç video indirilmiyor |
| Testler | 34/34 |

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
4. Yönetim paneli: şemadaki `status` alanı (`new` / `read` / `archived`) bunun
   için hazır, arayüz yok.

---

## 7. Marka kullanımı hakkında

Varsayılan tema Bosch kurumsal kimliğini kullanır (gerçek amblem, resmi renkler).
Bu **bağımsız bir prototiptir**, Robert Bosch GmbH ile bağlantısı yoktur ve
sayfa altında bu bilgilendirme görünür. Marka hakları Robert Bosch GmbH'ye aittir.

Kendi markanla kullanmak istersen `.env` içinde `APP_BRAND=kalibre` yeterli;
ya da `app/Support/Brand.php` içine yeni bir dizi ve `resources/css/app.css`
içine üç CSS değişkeni ekleyerek yeni bir tema tanımlayabilirsin.
