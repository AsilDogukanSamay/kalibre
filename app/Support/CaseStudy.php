<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Vaka çalışması içeriği. Ürün kararlarını ve ölçülen sonuçları taşır.
 * Sayılar uydurma değildir; her biri projede gerçekten ölçülmüştür.
 */
final class CaseStudy
{
    /** @return array<string,mixed> */
    public static function all(): array
    {
        return [
            'intro' => [
                'role'     => 'Ürün tasarımı, arayüz geliştirme ve backend',
                'duration' => 'Tek oturum',
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
                    'tradeoff' => 'İki video 7,7 MB. Mobilde hiç indirilmeyerek çözüldü.',
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
            ],

            /* -------------------------------------------------- Ölçümler */
            'metrics' => [
                [
                    'value' => '5,2:1',
                    'label' => 'Hero yazı kontrastı',
                    'note'  => 'Videonun 10 karesi × 4 ekran genişliğinde en kötü değer. WCAG AA eşiği 4,5.',
                ],
                [
                    'value' => '0',
                    'label' => 'Inline stil',
                    'note'  => 'Canlı DOM\'da style attribute\'u taşıyan eleman sayısı. Test otomatik doğruluyor.',
                ],
                [
                    'value' => '393 KB',
                    'label' => 'Mobil veri',
                    'note'  => '640px altında iki video da indirilmiyor. Kaynak data-src ile tutulur, koşul sağlanmazsa tek bayt inmez.',
                ],
                [
                    'value' => '22',
                    'label' => 'Geçen test',
                    'note'  => 'Doğrulama, XSS, prepared statement ve şablon katmanları. Harici bağımlılık yok.',
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
            ],

            'next' => [
                'Yönetim paneli: gelen talepleri durum akışıyla (yeni / okundu / arşiv) takip etmek. Şema buna hazır.',
                'Randevu takvimi: müsait slot seçimi, çift rezervasyon kilidi.',
                'Ölçüm raporunun PDF çıktısı; teslimde verilen dosyanın dijital eşi.',
                'Gerçek atölye çekimi: mevcut görseller yapay zeka üretimi, before/after kareleri gerçek işle değiştirilmeli.',
            ],
        ];
    }
}
