# Kalibre - görsel promptları

Sayfada 6 görsel slotu var. Hepsi tek bir ışık dilinde olmak zorunda,
yoksa sayfa dağılır. Bu yüzden önce ortak stil bloğu, sonra slot promptları.

---

## 1. Ortak stil bloğu

Her promptun SONUNA olduğu gibi ekle:

```
shot on a full frame camera, 50mm lens, low key automotive detailing studio,
charcoal grey walls and polished concrete floor, a single cool blue LED
inspection lamp is the only saturated colour in the frame, everything else
desaturated graphite and off white, deep shadows, no warm orange or amber
light anywhere, realistic documentary photography, subtle film grain,
natural imperfections, not a 3D render
```

Neden: sayfanın tek aksan rengi kobalt mavi (#4B7BFF). Görsellerde turuncu
atölye ışığı olursa sayfa iki renkli hale gelir ve ucuzlar.

## 2. Negatif prompt

Destekleyen araçlarda (Flux, SD, MJ `--no`) her seferinde kullan:

```
warm orange lighting, amber, golden hour, sunset, teal and orange grading,
white showroom background, lens flare, bokeh balls, HDR, oversaturated,
purple, neon, text, watermark, logo, brand badges, readable license plate,
faces, 3D render, CGI, plastic, cartoon, illustration
```

---

## 3. Slot promptları

### A. Hero - `img/hero-atolye.jpg`
**Oran 4:5, çıktı 1200x1500**

```
Close three quarter view of a dark graphite car bonnet inside a detailing
workshop. A handheld blue LED inspection light rakes across the paint at a
low angle and reveals fine swirl marks in the clear coat. A gloved hand
enters the frame from the right holding a small paint thickness gauge
against the panel. The background falls off into black. Vertical composition,
the panel fills the lower two thirds.
```

### B. Before / after - `img/kaput-sonrasi.jpg`
**Oran 16:9, çıktı 1600x900. ÖNCE BUNU ÜRET.**

```
Overhead three quarter shot of a glossy dark graphite car bonnet after paint
correction. The clear coat is mirror like and reflects one long softbox strip
running diagonally across the panel. No swirl marks, no haze, the reflection
edge is razor sharp. Workshop ceiling faintly visible in the reflection.
Horizontal composition, the panel centred and filling the frame.
```

### C. Before / after - `img/kaput-oncesi.jpg`
**Aynı kare, 1600x900. YENİDEN ÜRETME, B'yi düzenle.**

Sebebi: iki görselin milimetrik aynı açıda olması şart, sürgü kaydığında
kare oynarsa etki tamamen kaybolur. Yapay zeka iki tutarlı kare vermez.

Yöntem, B dosyasının kopyası üzerinde:
- Clarity ve Texture eksiye
- Doygunluk yaklasik %35 asagi
- Kontrast bir tık aşağı, siyahları kaldır (mat görünüm)
- Üstüne dairesel ince beyaz çizgi katmanı, opaklık %12 ile 18, soft light modu
- Hafif gauss blur, 1 ile 2 piksel

Görüntüden üretim (img2img) kullanacaksan strength 0.25'i geçme ve şu promptu ver:

```
same bonnet, same camera angle, same framing, but the clear coat is dull and
hazy with dense circular swirl marks and holograms, reflection broken up,
no gloss depth
```

### D. Scroll videosu posteri - `img/paso.jpg`
**Oran 4:3, çıktı 1600x1200. Videonun ilk karesiyle aynı sahne olmalı.**

```
A dual action polisher with a foam cutting pad working on a dark car bonnet.
White compound residue is spread in arcs across the panel. A blue inspection
lamp lights the wet paint from a low side angle. The operator's forearm shows
slight motion blur, the machine head is sharp. Close up, workshop interior
dissolving into darkness behind.
```

### E. Bento büyük hücre - `img/bento-duzeltme.jpg`
**Oran 4:3, çıktı 1200x900. ALT ÜÇTE BİRİ BOŞ VE KOYU OLMALI.**

Üstüne başlık ve metin biniyor, kalabalık olursa yazı okunmaz.

```
A detailing technician in a black apron polishing the rear quarter panel of a
dark sports car, machine polisher in both hands, seen from behind and slightly
above. A blue inspection lamp on a tripod stands further back in the workshop.
The lower third of the frame is empty dark polished concrete floor with no
detail in it.
```

### F. Bento geniş hücre - `img/bento-film.jpg`
**Oran 12:7, çıktı 1200x700. ALT ÜÇTE BİRİ BOŞ VE KOYU OLMALI.**

```
Gloved hands pressing a clear paint protection film onto a car's front bumper
with a squeegee. The film edge catches a thin highlight, slip solution beads
under the surface. Tight close up on the hands and the film edge. Dark studio
background. The lower third of the frame is empty and unlit.
```

### G. Paylaşım kapağı - `img/og-kapak.jpg`
**Oran 1.91:1, çıktı 1200x630**

Sayfada su an `og:image` yok, ekle. Link paylaşıldığında görünen kare bu.

```
Wide low angle shot of a dark graphite car bonnet under a single blue
inspection lamp. The lamp sits on the right, the left half of the frame is
almost entirely dark empty space. Strong horizontal composition.
```

---

## 4. Midjourney parametreleri

Prompt metninin sonuna, kendi sürüm bayrağını ekleyerek:

```
A  --ar 4:5   --style raw --s 150 --no warm orange lighting, text, watermark, faces
B  --ar 16:9  --style raw --s 100 --no warm orange lighting, text, watermark, swirl marks
D  --ar 4:3   --style raw --s 150 --no warm orange lighting, text, watermark
E  --ar 4:3   --style raw --s 150 --no warm orange lighting, text, watermark
F  --ar 12:7  --style raw --s 150 --no warm orange lighting, text, watermark
G  --ar 191:100 --style raw --s 100 --no warm orange lighting, text, watermark
```

`--style raw` önemli. Onsuz Midjourney fazla sinematik ve turuncu bir hava katıyor.

Flux, Seedream, Nano Banana gibi araçlarda parametre yerine oranı doğrudan
arayüzden seç, prompt metni aynen çalışır.

---

## 5. Gerçek çekim yapacaksan

Yapay zeka görseli hero ve doku kareleri için yeterli, ama before / after
için değil. Gerçek bir işletmede yapılmamış bir işin öncesi sonrası görseli
üretilmiş olur, bu müşteriye yanlış beyandır.

Çekim listesi, tek bir araçla yarım gün:
1. Araç girişte, yıkanmadan, mavi lamba ile swirl gösteren 3 kare
2. Kalınlık ölçer panelin üstünde, ekran okunacak kadar yakın, 2 kare
3. Pasta makinesi çalışırken, aynı panel, 4 kare
4. Aynı panel bitmiş halde, TRIPODU VE IŞIĞI HİÇ OYNATMADAN, 3 kare
5. Film uygulaması, eldivenli eller, 3 kare
6. Atölye geneli, geniş, 2 kare

4. madde kritik. Öncesi ve sonrası aynı tripod pozisyonundan çekilmezse
slider işe yaramaz. Ölçüm başlamadan tripodu kur ve iş bitene kadar dokunma.

---

## 6. Dosyaları yerine koyma

Görselleri `img/` klasörüne yukarıdaki adlarla kaydet, sonra `index.html`
içinde picsum ile başlayan 5 adresi değiştir:

| Şu anki adres | Yenisi |
|---|---|
| `...seed/kalibre-atolye/1200/1500` | `img/hero-atolye.jpg` |
| `...seed/kalibre-kaput/1600/900?grayscale&blur=2` | `img/kaput-oncesi.jpg` |
| `...seed/kalibre-kaput/1600/900` | `img/kaput-sonrasi.jpg` |
| `...seed/kalibre-paso/1600/1200` | `img/paso.jpg` |
| `...seed/kalibre-duzeltme/1200/900` | `img/bento-duzeltme.jpg` |
| `...seed/kalibre-film/1200/700` | `img/bento-film.jpg` |

Kapak görseli için `<head>` içine ekle:

```html
<meta property="og:image" content="https://alanadiniz.com/img/og-kapak.jpg">
```

Yüklemeden önce hepsini WebP'ye çevir ve 250 KB altına indir, yoksa sayfa
açılış süresi düşer. Squoosh.app tek tek için yeterli.
