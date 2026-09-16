<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Sayfa içeriği tek kaynaktan gelir. Şablonlarda dizi tekrarı olmaz (DRY),
 * içerik değişikliği için HTML'e dokunmak gerekmez.
 */
final class SiteContent
{
    /** @return array<string,mixed> */
    public static function all(): array
    {
        return [
            'brand' => [
                'name'     => 'Kalibre',
                'service'  => 'Boya Düzeltme ve Seramik Kaplama',
                'phone'    => '+90 212 347 01 18',
                'whatsapp' => '902123470118',
                'address'  => 'Ayazağa Mah. Kemerburgaz Cad. No 14, Maslak, İstanbul',
                'hours'    => 'Hafta içi 09.00 - 19.00, Cumartesi 10.00 - 16.00',
                'maps'     => 'https://www.google.com/maps/search/?api=1&query=Ayaza%C4%9Fa+Mah.+Kemerburgaz+Cad.+No+14+Maslak+%C4%B0stanbul',
                'geo'      => ['lat' => 41.1105, 'lng' => 29.0203],
            ],

            'hero' => [
                'title'     => 'Boyayı ölçerek düzeltiyoruz.',
                'subtitle'  => "İstanbul Maslak'ta boya düzeltme ve seramik kaplama atölyesi. Her araç mikron ölçümüyle başlar, ölçüm raporuyla teslim edilir.",
                'primary'   => ['label' => 'Randevu al', 'href' => '#iletisim'],
                'secondary' => ['label' => 'İşlerimizi gör', 'href' => '#calismalar'],
            ],

            'stats' => [
                ['value' => '1.480',    'label' => '2014 yılından bu yana işlenen araç'],
                ['value' => '11 nokta', 'label' => 'Her araçta alınan kalınlık ölçümü'],
                ['value' => '36 ay',    'label' => 'Seramik kaplama garanti süresi'],
            ],

            'services' => [
                [
                    'title' => 'Boya düzeltme',
                    'price' => "24.900 TL'den başlar",
                    'body'  => 'İki veya üç kademeli kesme ve parlatma. Swirl, hologram ve hafif çizikler kalkar, kalan vernik her adımda ölçülür.',
                    'meta'  => '1 ile 3 gün',
                    'image' => 'bento-duzeltme.webp',
                    'size'  => 'lg',
                ],
                [
                    'title' => 'Seramik kaplama',
                    'price' => "18.500 TL'den başlar",
                    'body'  => '9H sınıfı kaplama, düzeltme bittikten sonra kontrollü ortamda uygulanır. Yıkama direnci ve su itme performansı kayıt altına alınır.',
                    'meta'  => '36 ay garanti',
                    'image' => 'bento-seramik.webp',
                    'size'  => 'md',
                ],
                [
                    'title' => 'İç detaylı temizlik',
                    'price' => "6.400 TL'den başlar",
                    'body'  => 'Deri, alkantara ve tekstil için ayrı kimyasal. Ekstraksiyon sonrası koku giderme ve UV koruma.',
                    'meta'  => '6 ile 10 saat',
                    'image' => 'bento-ic-temizlik.webp',
                    'size'  => 'md',
                ],
                [
                    'title' => 'Şeffaf koruma filmi',
                    'price' => "32.000 TL'den başlar",
                    'body'  => 'Ön tampon, kaput ve ayna kapakları için kesim şablonuyla uygulanan self healing film. Taş çiziklerini fiziksel olarak durdurur.',
                    'meta'  => '2 ile 4 gün',
                    'image' => 'bento-film.webp',
                    'size'  => 'lg',
                ],
            ],

            /*
             * Sureç adimlari. Her adimda uc soru cevaplanir:
             * ne yapiliyor (body), ne kadar suruyor (time) ve
             * musterinin eline ne geciyor (output). Ucuncusu eklendi:
             * "ne oluyor" sorusunun karsiligi zaten vardi, "bana ne
             * kaliyor" sorusununki yoktu.
             *
             * image: public/assets/img altinda varsa kullanilir, yoksa
             * numara plakasina dusulur (services bolumuyle ayni kural).
             */
            'process' => [
                [
                    'title'  => 'Ölçüm',
                    'body'   => '11 noktadan kalınlık, panel panel parlaklık ve inceleme lambası altında hasar kaydı.',
                    'time'   => '45 dakika',
                    'output' => 'Ölçüm tablosu ve hasar haritası',
                    'image'  => 'surec-olcum.webp',
                ],
                [
                    'title'  => 'Dekontaminasyon',
                    'body'   => 'İki kovalı yıkama, kil ve demir tozu sökücü. Yüzeyde iz bırakan ne varsa düzeltmeden önce gider.',
                    'time'   => '3 saat',
                    'output' => 'Temizlenen yüzeyin öncesi/sonrası kaydı',
                    'image'  => 'surec-dekontaminasyon.webp',
                ],
                [
                    'title'  => 'Düzeltme',
                    'body'   => 'Test alanında pad ve pasta seçimi, ardından kademeli pasolar. Her paso sonrası yeniden ölçüm.',
                    'time'   => '1 ile 3 gün',
                    'output' => 'Paso başına kalınlık ve parlaklık kaydı',
                    'image'  => 'surec-duzeltme.webp',
                ],
                [
                    'title'  => 'Koruma ve teslim',
                    'body'   => 'Kaplama uygulanır, 12 saat kürlenir. Teslimde ölçüm raporu ve bakım takvimi birlikte verilir.',
                    'time'   => '1 gün',
                    'output' => 'Ölçüm raporu, garanti belgesi ve bakım takvimi',
                    'image'  => 'surec-teslim.webp',
                ],
            ],

            /* Sürecin toplamı. Musterinin ilk sordugu sey aracin kac gun
               atolyede kalacagi; bu bilgi adimlarin icine gomulu kalmasin. */
            'processSummary' => [
                'total' => '2 ile 5 iş günü',
                'note'  => 'Süreyi belirleyen tek şey düzeltme aşaması: boyanın durumu kaç paso gerektiriyorsa süre ona göre uzuyor. Ölçüm bittiğinde net gün sayısını söylüyoruz.',
            ],

            'plans' => [
                [
                    'name'     => 'Koruma',
                    'intro'    => 'Boyası iyi durumda olan, çizilmeden önce korumaya almak isteyen araçlar için.',
                    'price'    => '18.500',
                    'currency' => 'TL',
                    'featured' => false,
                    'items'    => [
                        '11 noktadan kalınlık ve parlaklık ölçümü',
                        'Dekontaminasyon ve tek kademe parlatma',
                        '9H seramik kaplama, 36 ay garanti',
                        'Ölçüm raporu ve bakım takvimi',
                    ],
                ],
                [
                    'name'     => 'Tam düzeltme',
                    'intro'    => 'Hologram, swirl ve mat görünüm varsa. Boyanın altındaki derinliği geri getirir.',
                    'price'    => '34.900',
                    'currency' => 'TL',
                    'featured' => true,
                    'items'    => [
                        'Koruma paketindeki her şey',
                        'İki ile üç kademeli kesme ve parlatma',
                        'Paso arası kalınlık takibi, kalan vernik kaydı',
                        'Far parlatma ve cam yağmur kaplaması',
                        'Ön tampon şeffaf film opsiyonu',
                    ],
                ],
            ],

            /*
             * Referanslar. 'rating' anahtari kaldirildi: bes kirmizi yildiz her
             * sitede ayni sekilde duruyor ve hicbir sey olcmuyor. Yerine o araca
             * ait OLCUM sonucu geldi ('olcum'). Sayfanin geri kalani neyi
             * yapiyorsa - iddiayi rakama baglamak - sosyal kanit da onu yapiyor.
             *
             * Uc ayri olcum turu bilincli: kalinlik, su temas acisi ve parlaklik.
             * Boylece bolum "ayni rakamin uc kez tekrari" gibi okunmuyor.
             */
            'testimonials' => [
                [
                    'quote' => 'Teslimde elime bir rapor verdiler. Hangi panelde kaç mikron kaldığını görünce ikinci pasoyu neden yapmadıklarını anladım.',
                    'name'  => 'Deniz Ülgen',
                    'role'  => 'Porsche 911, 2019 model',
                    'olcum' => ['key' => 'Kaput, kalan vernik', 'value' => '152 → 141', 'unit' => 'µm'],
                ],
                [
                    'quote' => 'Üç yıldır aynı yerdeyim. Kaplama hâlâ su tutmuyor, yıkama süresi yarıya indi.',
                    'name'  => 'Melis Arıkan',
                    'role'  => 'BMW M340i, 2022 model',
                    'olcum' => ['key' => 'Su temas açısı, 3. yıl', 'value' => '104° → 98°', 'unit' => ''],
                ],
                [
                    'quote' => 'Fiyatı önceden net söylediler, teslimde değişmedi. Randevu talebime de aynı gün içinde döndüler.',
                    'name'  => 'Kerem Batur',
                    'role'  => 'Volvo XC60, 2023 model',
                    'olcum' => ['key' => 'Parlaklık, kapı paneli', 'value' => '38 → 91', 'unit' => 'GU'],
                ],
            ],

            'faq' => [
                [
                    'q' => 'Boya düzeltme boyayı inceltir mi?',
                    'a' => 'Evet, kesme pasosu vernikten birkaç mikron alır. Bu yüzden her paso öncesi ve sonrası kalınlık ölçüyoruz. Kalan vernik güvenli sınırın altına inecekse ikinci pasoya geçmiyor, durumu size yazılı bildiriyoruz.',
                ],
                [
                    'q' => 'Seramik kaplama çizilmeyi tamamen engelliyor mu?',
                    'a' => 'Hayır. Kaplama kimyasal lekelere, kuş pisliğine ve hafif yıkama izlerine karşı direnç sağlar, taş çiziğini durdurmaz. Fiziksel darbe koruması istiyorsanız şeffaf koruma filmi gerekir.',
                ],
                [
                    'q' => 'Araç kaç gün atölyede kalır?',
                    'a' => 'Koruma paketi iki gün, tam düzeltme üç ile dört gün sürer. Kaplama sonrası kürlenme için aracın bir gece atölyede kalması gerekiyor.',
                ],
                [
                    'q' => 'Yeni araçta düzeltmeye gerek var mı?',
                    'a' => 'Çoğu sıfır araçta nakliye ve bayi yıkamasından gelen hafif izler oluyor. Ölçümü ücretsiz yapıyoruz, gerek yoksa doğrudan koruma paketine geçiyoruz.',
                ],
            ],
        ];
    }
}
