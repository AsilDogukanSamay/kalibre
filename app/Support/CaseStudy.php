<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Vaka çalışması içeriği. Ürün kararlarını ve ölçülen sonuçları taşır.
 * Sayılar uydurma değildir; her biri projede gerçekten ölçülmüştür.
 */
final class CaseStudy
{
    /**
     * Video klasorunun toplam boyutu, dosyalardan hesaplanir.
     * Onceki halde "7,7 MB" diye ELLE yaziliydi ve gercek 6,4 MB'ydi: klip
     * degisince rakam sessizce eskimisti. Beyan artik kaynagi okuyor.
     */
    private static function videoBoyutu(): string
    {
        $toplam = 0;
        foreach (glob(dirname(__DIR__, 2) . '/public/assets/video/*.mp4') ?: [] as $dosya) {
            $toplam += (int) filesize($dosya);
        }

        return number_format($toplam / 1048576, 1, ',', '.') . ' MB';
    }

    /** @return array<string,mixed> */
    public static function all(): array
    {
        return [
            'intro' => [
                'role'     => 'Ürün tasarımı, arayüz geliştirme ve backend',
                'duration' => 'Birkaç oturum',
                'stack'    => 'Tailwind CSS · PHP 8.4 (OOP/MVC) · MySQL · Vanilla JS',
                'summary'  => 'Boya düzeltme atölyeleri işlerini kelimeyle anlatır: "titiz çalışırız", "kaliteli malzeme". Müşteri bunu doğrulayamaz. Bu sayfa iddiayı ölçüme çevirir.',
            ],

            /* -------------------------------------------------- Problem */
            'problem' => [
                [
                    'title' => 'Hizmet görünmez',
                    'body'  => 'Boya düzeltmenin çıktısı fotoğrafta belli olmaz. Müşteri neye para verdiğini anlamadığı için fiyat pazarlığa döner.',
                ],
                [
                    'title' => 'Güven kanıtlanamaz',
                    'body'  => 'Her atölye aynı cümleleri kurar. Ayrışmanın tek yolu doğrulanabilir bir çıktı sunmaktır.',
                ],
                [
                    'title' => 'Talep telefonda kaybolur',
                    'body'  => 'Randevu akışı WhatsApp ve telefon arasında dağılır, takip edilemez, ölçülemez.',
                ],
            ],

            /* -------------------------------------------------- Kararlar */
            'decisions' => [
                [
                    'n'        => '01',
                    'title'    => 'Sayfanın omurgası mikron ölçümü',
                    'decision' => 'Dekoratif istatistik kullanılmadı. Hero, karşılaştırma sürgüsü ve scroll bölümü aynı veriyi taşıyor: boya kalınlığı 138 → 129 µm, parlaklık 41 → 94 GU.',
                    'why'      => 'Rakam tekrar ettikçe iddia değil ölçüm gibi okunuyor. Ziyaretçi sayfadan tek bir cümleyle ayrılıyor: burası ölçerek çalışıyor.',
                    'tradeoff' => 'Serbest pazarlama dili kaybedildi. Karşılığında fiyat konuşması ölçüm üzerinden başlıyor.',
                ],
                [
                    'n'        => '02',
                    'title'    => 'Göstermek, anlatmaya tercih edildi',
                    'decision' => 'Öncesi/sonrası sürgüsü aynı tripod pozisyonundan çekilmiş iki kareyi kullanıyor. Scroll bölümünde video, scroll ilerlemesine bağlı; kullanıcı pasoyu kendi hızında izliyor.',
                    'why'      => 'Hizmetin çıktısı ancak hareket ve karşılaştırmayla anlaşılıyor. Kullanıcı kontrolü elinde tuttuğu için de izlemek yerine inceliyor.',
                    'tradeoff' => 'İki video ' . self::videoBoyutu() . '. Mobilde hiç indirilmeyerek çözüldü.',
                ],
                [
                    'n'        => '03',
                    'title'    => 'Cam yüzeyler hareketli görüntü üzerinde',
                    'decision' => 'Glassmorphism dekor olarak değil, hero videosunun üzerinde bilgi katmanı olarak kullanıldı.',
                    'why'      => 'Buzlu cam ancak arkasında okunacak bir şey varken anlam kazanıyor. Düz zemin üzerinde aynı efekt sadece gürültü.',
                    'tradeoff' => 'Okunabilirlik riski arttı; perde ölçülerek ayarlandı (aşağıdaki metrik).',
                ],
                [
                    'n'        => '04',
                    'title'    => 'Tasarım sistemi markadan bağımsız',
                    'decision' => 'Renkler CSS değişkenlerine, Tailwind token\'ları o değişkenlere bağlandı. Marka değiştirmek tek bir .env satırı.',
                    'why'      => 'Ajans işinde aynı iskelet farklı markalara giydirilir. Sınıf adlarına marka gömmek her projede yeniden yazmak demek.',
                    'tradeoff' => 'Bir soyutlama katmanı eklendi. Karşılığında marka değişimi sıfır dosya değişikliğiyle oluyor.',
                ],
                [
                    'n'        => '05',
                    'title'    => 'Form sayfayı terk etmiyor',
                    'decision' => 'Gönderim fetch ile; sonuç glassmorphic toast ve alan bazlı hata mesajlarıyla dönüyor. Başarıda takip numarası veriliyor.',
                    'why'      => 'Sayfa yenilemesi kullanıcıyı hero videosundan ve okuduğu bağlamdan koparıyor. Takip numarası da soyut bir "teşekkürler" yerine somut bir çıktı.',
                    'tradeoff' => 'JavaScript kapalıysa form çalışmaz. Telefon ve WhatsApp her ekranda erişilebilir tutularak dengelendi.',
                ],
                [
                    'n'        => '06',
                    'title'    => 'Sönük yazı yok, hiyerarşi tipografiyle kuruluyor',
                    'decision' => 'Nötr gri metinler için eşik AA (4,5:1) değil AAA (7:1) yapıldı ve sayfanın en açık nötr yüzeyinde ölçülüyor. İçerik taşıyan açıklamalar 12 pikselik ipucu kademesinden çıkarılıp kendi bileşenlerine alındı.',
                    'why'      => 'AA eşiği okunabilirliğin tabanıdır, hedefi değil. Ölçüm geçiyordu ama gözle bakınca soluk gri 12 piksel paragraflar hâlâ zayıf duruyordu. Bir metni geri plana atmanın yolu soldurmak değil, punto ve ağırlıkla söylemek.',
                    'tradeoff' => 'Gri kademeler arasındaki luminans farkı neredeyse kapandı; ayrımı artık punto ve harf aralığı yapıyor. Marka kırmızısı bu eşiğe çıkamadığı için kullanımı kısa etiketlerle sınırlandı.',
                ],
                [
                    'n'        => '07',
                    'title'    => 'Kurumsal paletin üçü de rol aldı',
                    'decision' => 'Primary kırmızı marka ve eylem rengi olarak kaldı; secondary ve accent mavileri ölçüm diline verildi. Gösterge kanalı secondary, canlı ölçüm değeri ve ışık şeridi accent.',
                    'why'      => 'Tek aksanla kalmak paleti eksik kullanmak olurdu. Renklere rol vermek, ikisini de kullanmanın dağıtmayan yolu: kırmızı "bir şey yap" der, mavi "bir şey ölçülüyor" der.',
                    'tradeoff' => 'İki renk ailesi yönetmek gerekiyor. Rol ayrımı net olduğu için sayfada karışmıyorlar.',
                ],
                [
                    'n'        => '08',
                    'title'    => 'Kişisel veri toplayan form, aydınlatma metni olmadan yayına çıkamaz',
                    'decision' => 'KVKK aydınlatma metni ve gizlilik politikası sayfaları eklendi, formda onay kutusu zorunlu kılındı. Onayın anı ve onaylanan metnin sürümü kayıtla birlikte saklanıyor.',
                    'why'      => 'Yalnızca "onay alındı" demek ispat değildir. Metin zamanla değişir; hangi kaydın hangi metne onay verdiği bilinmiyorsa ispat yükümlülüğü karşılanmaz. Sürüm numarası bunu çözüyor.',
                    'tradeoff' => 'Formda bir adım daha. Dönüşümde küçük bir kayıp karşılığında sayfa gerçekten yayına çıkabilir hâle geldi.',
                ],
                [
                    'n'        => '09',
                    'title'    => 'Talebi alan vardı, haber veren yoktu',
                    'decision' => 'Gönderim sonrası atölyeye bildirim katmanı ve gelen talepleri durum akışıyla yöneten bir panel eklendi. Şemadaki status alanı zaten bunun için hazırdı.',
                    'why'      => 'Form veritabanına yazıyordu ama kimse haberdar olmuyordu. Hizmete açık bir sayfada bu, cevaplanmayan randevu demektir.',
                    'tradeoff' => 'Panel yeni bir saldırı yüzeyi açıyor. Oturum yenileme, CSRF belirteci, IP başına giriş freni ve açık yönlendirme koruması bu yüzden aynı anda yazıldı.',
                ],
                [
                    'n'        => '10',
                    'title'    => 'Çerez onayı yerine çerez kullanmamak',
                    'decision' => 'Ziyaretçi tarafında tek çerez oluşturulmuyor; onay penceresi de yok. Oturum çerezi yalnızca yönetim paneli yolunda oluşuyor.',
                    'why'      => 'Onay penceresi bir çözüm değil, bir bedeldir. Çerez gerçekten gerekmiyorsa doğru cevap pencereyi güzelleştirmek değil, çerezi kaldırmaktır.',
                    'tradeoff' => 'Sayfada analitik yok. Ölçüm gerekirse çerezsiz bir analitik eklenmeli ve gizlilik metni de o gün güncellenmeli.',
                ],
            ],

            /* -------------------------------------------------- Ölçümler */
            'metrics' => [
                [
                    'value' => '4,55:1',
                    'label' => 'Hero yazı kontrastı',
                    'note'  => 'Videonun 10 karesi, üç ekran genişliği, hero içindeki her metin elemanı. En kötü değer bu; WCAG AA eşiği 4,5. Yazı gizlenip kare fotoğraflanarak ölçüldü, hesaplanmadı.',
                ],
                [
                    'value' => '0',
                    'label' => 'Inline stil',
                    'note'  => 'Canlı DOM\'da style attribute\'u taşıyan eleman sayısı. Test otomatik doğruluyor.',
                ],
                [
                    'value' => '503 KB',
                    'label' => 'Mobil veri',
                    'note'  => '390 pikselde ilk yükleme, ham gövde boyutu; gzip ile 382 KB. Video isteği sıfır. Hero posteri de dar ekranda 960 piksellik sürümüyle iniyor.',
                ],
                [
                    // Bu rakam elle guncellenmez: tests/run.php sonunda kendi
                    // toplamiyla karsilastiriliyor, eskirse test kaliyor.
                    'value' => '295',
                    'label' => 'Geçen test',
                    'note'  => 'Doğrulama, kurumsal kimlik, güvenlik başlıkları, CSRF, giriş freni, prepared statement ve şablon katmanları. Harici bağımlılık yok.',
                ],
                [
                    'value' => '0',
                    'label' => 'Eşik altı metin',
                    'note'  => 'Kontrast denetimi iki genişlikte, panel dahil altı sayfada çalıştırıldı. WCAG AA eşiğinin altında kalan metin yok.',
                ],
                [
                    'value' => '1',
                    'label' => 'Denetimin bulduğu kusur',
                    'note'  => 'Panelde telefon ve e-posta bağlantıları 12 piksel boyutunda 4,44:1 veriyordu. Renk değil rol değişti: yazı ink oldu, marka rengi alt çizgide kaldı.',
                ],
                [
                    'value' => '0',
                    'label' => 'Ziyaretçi çerezi',
                    'note'  => 'Onay penceresi bu yüzden yok. Oturum çerezi yalnızca yönetim paneli yolunda oluşuyor.',
                ],
                [
                    'value' => '0',
                    'label' => 'Yerleşim kusuru',
                    'note'  => 'Altı genişlik, beş sayfa: yatay taşma yok, her sayfada tek h1, başlık atlaması yok, alt metni olmayan görsel yok.',
                ],
            ],

            /* -------------------------------------------------- Sistem */
            'scale' => [
                ['token' => 'display', 'wdth' => '118', 'wght' => '700', 'use' => 'Başlıklar'],
                ['token' => 'body',    'wdth' => '92',  'wght' => '400', 'use' => 'Gövde metni'],
                ['token' => 'readout', 'wdth' => '70',  'wght' => '600', 'use' => 'Ölçüm okumaları'],
            ],

            'architecture' => [
                ['layer' => 'public/index.php', 'role' => 'Tek giriş noktası. İstek yakalanır, yönlendiriciye verilir.'],
                ['layer' => 'Core\\Router',      'role' => 'Metot + yol eşleştirir, denetleyiciyi çağırır.'],
                ['layer' => 'Controllers',      'role' => 'İş akışını yönetir. Sorgu yazmaz, HTML basmaz.'],
                ['layer' => 'Core\\Validator',  'role' => 'Kural tabanlı doğrulama, girdi temizliği.'],
                ['layer' => 'Models',           'role' => 'Tek SQL noktası. Yalnızca prepared statement.'],
                ['layer' => 'Core\\View',       'role' => 'Şablon + XSS kaçışı. Kaçışsız değer basılmaz.'],
                ['layer' => 'Core\\Response',   'role' => 'Tek JSON sözleşmesi. Tüm uçlar aynı biçimde döner.'],
                ['layer' => 'Core\\Security',   'role' => 'CSP ve güvenlik başlıkları. Nonce istek başına üretilir.'],
                ['layer' => 'Core\\Session',    'role' => 'Panel oturumu ve CSRF belirteci. Ziyaretçi tarafında hiç çalışmaz.'],
                ['layer' => 'Support',          'role' => 'İçerik ve yan servisler: metinler, yasal metinler, marka, bildirim, giriş freni.'],
            ],

            'next' => [
                'Randevu takvimi: müsait slot seçimi, çift rezervasyon kilidi.',
                'Ölçüm raporunun PDF çıktısı; teslimde verilen dosyanın dijital eşi.',
                'Gerçek atölye çekimi: mevcut görseller yapay zeka üretimi, before/after kareleri gerçek işle değiştirilmeli.',
                'Veri sorumlusu bilgileri: ticaret unvanı, MERSİS ve KEP adresi yasal metne eklenmeli.',
                'Bildirim şu an günlük dosyasına yazıyor; gerçek e-posta için SMTP yapılandırması gerekiyor.',
            ],
        ];
    }
}
