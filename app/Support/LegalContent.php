<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Yasal metinler tek kaynakta durur.
 *
 * Surum numarasi onemlidir: forma verilen onay, onaylanan metnin surumuyle
 * birlikte kaydedilir (contact_messages.consent_version). Metin degistiginde
 * SURUM yukseltilir, boylece hangi kaydin hangi metne onay verdigi bellidir.
 * KVKK'nin ispat yukumlulugu pratikte bunu gerektirir.
 */
final class LegalContent
{
    public const SURUM = '2026.09';

    /** Metnin son guncellenme tarihi (sayfada gorunur). */
    public const GUNCELLEME = '16 Eylül 2026';

    /**
     * Sayfa kayitlari. Rota, baslik ve uretici metot tek yerde eslesir;
     * yeni bir yasal metin eklemek bu diziye bir satir eklemektir.
     *
     * @return array<string,array{path:string,title:string,nav:string,method:string}>
     */
    public static function pages(): array
    {
        return [
            'aydinlatma' => [
                'path'   => '/kvkk',
                'title'  => 'KVKK Aydınlatma Metni',
                'nav'    => 'KVKK aydınlatma metni',
                'method' => 'aydinlatma',
            ],
            'gizlilik' => [
                'path'   => '/gizlilik',
                'title'  => 'Gizlilik ve Çerez Politikası',
                'nav'    => 'Gizlilik ve çerez politikası',
                'method' => 'gizlilik',
            ],
        ];
    }

    /**
     * 6698 sayili Kisisel Verilerin Korunmasi Kanunu'nun 10. maddesinde
     * sayilan baslik sirasina gore duzenlenmistir.
     *
     * @param array<string,mixed> $brand
     * @return array<string,mixed>
     */
    public static function aydinlatma(array $brand): array
    {
        $unvan   = (string) ($brand['name'] ?? '');
        $adres   = (string) ($brand['address'] ?? '');
        $telefon = (string) ($brand['phone'] ?? '');

        return [
            'title'    => 'KVKK Aydınlatma Metni',
            'lead'     => '6698 sayılı Kişisel Verilerin Korunması Kanunu’nun 10. maddesi ve Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uyulacak Usul ve Esaslar Hakkında Tebliğ uyarınca hazırlanmıştır.',
            'updated'  => self::GUNCELLEME,
            'version'  => self::SURUM,
            'sections' => [
                [
                    'heading' => 'Veri sorumlusunun kimliği',
                    'body'    => [
                        $unvan . ' (“atölye”), bu internet sitesi üzerinden ilettiğiniz kişisel verilerin veri sorumlusudur.',
                        'Adres: ' . $adres,
                        'Telefon: ' . $telefon,
                    ],
                    // Tuzel kisi bilgileri prototipte doldurulamaz; eksik oldugu
                    // gizlenmek yerine acikca isaretlenir.
                    'note' => 'Yayına alınmadan önce bu bölüme işletmenin ticaret unvanı, MERSİS numarası, '
                        . 'vergi dairesi ve KEP adresi eklenmelidir. Prototipte bu alanlar boş bırakılmıştır.',
                ],
                [
                    'heading' => 'İşlenen kişisel veriler',
                    'body'    => ['İletişim formu, randevu oluşturmak için gereken en az veriyi ister:'],
                    'list'    => [
                        'Kimlik verisi: ad ve soyad',
                        'İletişim verisi: telefon numarası, e-posta adresi',
                        'Müşteri işlem verisi: form mesajında ilettiğiniz araç ve hasar bilgisi',
                        'İşlem güvenliği verisi: IP adresi ve tarayıcı bilgisi',
                    ],
                ],
                [
                    'heading' => 'İşleme amaçları',
                    'list'    => [
                        'Randevu talebinizin alınması ve size dönüş yapılması',
                        'Ölçüm ve durum tespiti öncesinde araç hakkında ön bilgi edinilmesi',
                        'Talep ve şikâyetlerin takibi',
                        'Otomatik (bot) gönderimlerin engellenmesi ve sistem güvenliğinin sağlanması',
                    ],
                ],
                [
                    'heading' => 'Hukuki sebep',
                    'body'    => [
                        'Ad, soyad, telefon, e-posta ve mesaj içeriği; Kanun’un 5/2-(c) maddesi uyarınca, bir sözleşmenin kurulması veya ifasıyla doğrudan ilgili olması sebebiyle, talebiniz üzerine ön görüşme yapılabilmesi amacıyla işlenir.',
                        'IP adresi ve tarayıcı bilgisi, Kanun’un 5/2-(f) maddesindeki meşru menfaat sebebine dayanır. Yalnızca kötüye kullanımı sınırlamak için tutulur; pazarlama amacıyla kullanılmaz.',
                        'Formdaki onay kutusu, bu metnin size sunulduğunu kayıt altına alır. Onayın verildiği an ve metnin sürümü, talebinizle birlikte saklanır.',
                    ],
                ],
                [
                    'heading' => 'Verilerin aktarılması',
                    'body'    => [
                        'Kişisel verileriniz üçüncü kişilere satılmaz, pazarlama amacıyla paylaşılmaz.',
                        'Aktarım yalnızca iki durumda söz konusu olur: barındırma hizmeti alınan yurt içi sunucu sağlayıcısı (veri işleyen sıfatıyla) ve kanunen yetkili kamu kurum ve kuruluşlarının talebi.',
                        'Yurt dışına aktarım yapılmaz.',
                    ],
                ],
                [
                    'heading' => 'Toplama yöntemi',
                    'body'    => [
                        'Veriler, bu sitedeki iletişim formunu doldurup göndermeniz üzerine, tamamen otomatik yolla ve elektronik ortamda toplanır.',
                    ],
                ],
                [
                    'heading' => 'Saklama süresi',
                    'body'    => [
                        'Randevuya dönüşmeyen talepler en fazla 12 ay saklanır, süre sonunda silinir.',
                        'Hizmete dönüşen kayıtlar, ilgili mevzuattaki zamanaşımı ve saklama süreleri boyunca tutulur.',
                        'Süre dolduğunda veriler silinir, yok edilir veya anonim hâle getirilir.',
                    ],
                ],
                [
                    'heading' => 'İlgili kişi olarak haklarınız',
                    'body'    => ['Kanun’un 11. maddesi uyarınca başvurarak şunları talep edebilirsiniz:'],
                    'list'    => [
                        'Kişisel verinizin işlenip işlenmediğini öğrenme, işlenmişse buna ilişkin bilgi isteme',
                        'İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme',
                        'Yurt içinde veya yurt dışında verilerin aktarıldığı üçüncü kişileri bilme',
                        'Eksik veya yanlış işlenmişse düzeltilmesini isteme',
                        'Kanundaki şartlar çerçevesinde silinmesini veya yok edilmesini isteme',
                        'Düzeltme, silme ve yok etme işlemlerinin aktarım yapılan üçüncü kişilere bildirilmesini isteme',
                        'Münhasıran otomatik sistemlerle analiz edilmesi suretiyle aleyhinize bir sonuç çıkmasına itiraz etme',
                        'Kanuna aykırı işlenme sebebiyle zarara uğramanız hâlinde zararın giderilmesini talep etme',
                    ],
                ],
                [
                    'heading' => 'Başvuru yolu',
                    'body'    => [
                        'Başvurularınızı, Veri Sorumlusuna Başvuru Usul ve Esasları Hakkında Tebliğ’e uygun şekilde yazılı olarak yukarıdaki adrese iletebilir veya kayıtlı elektronik posta (KEP) adresine gönderebilirsiniz.',
                        'Başvurunuz en geç otuz gün içinde sonuçlandırılır. İşlemin ayrıca bir maliyet gerektirmesi hâlinde Kurul tarafından belirlenen tarifedeki ücret alınabilir.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string,mixed> $brand
     * @return array<string,mixed>
     */
    public static function gizlilik(array $brand): array
    {
        return [
            'title'    => 'Gizlilik ve Çerez Politikası',
            'lead'     => 'Bu sayfa, sitede hangi verinin tutulduğunu ve tarayıcınızda ne saklandığını açıklar.',
            'updated'  => self::GUNCELLEME,
            'version'  => self::SURUM,
            'sections' => [
                [
                    'heading' => 'Bu sitede çerez kullanılmıyor',
                    'body'    => [
                        'Ziyaretçi tarafında tek bir çerez dahi oluşturulmaz. Reklam, izleme, analiz veya sosyal medya çerezi yoktur; bu yüzden bir çerez onay penceresi de göremezsiniz.',
                        'Tek istisna yönetim panelidir: yalnızca atölye personeli giriş yaptığında oturumun açık kalmasını sağlayan zorunlu bir oturum çerezi oluşur. Bu çerez ziyaretçide oluşmaz, tarayıcı kapatıldığında silinir.',
                    ],
                ],
                [
                    'heading' => 'Ziyaret sırasında ne toplanıyor',
                    'body'    => [
                        'Formu göndermediğiniz sürece sizden hiçbir kişisel veri alınmaz. Sayfayı gezerken davranışınız izlenmez, profil çıkarılmaz.',
                        'Formu gönderdiğinizde, ilettiğiniz bilgilerin yanında IP adresiniz ve tarayıcı bilginiz kaydedilir. Bunun tek amacı otomatik gönderimleri sınırlamaktır.',
                    ],
                ],
                [
                    'heading' => 'Üçüncü taraf hizmetler',
                    'list'    => [
                        'Yazı tipleri Google Fonts üzerinden yüklenir; bu istek sırasında IP adresiniz Google sunucularına ulaşır.',
                        'WhatsApp ve harita bağlantıları yalnızca siz tıkladığınızda ilgili servise gider. Sayfada gömülü harita veya sosyal medya bileşeni yoktur.',
                        'Sayfada reklam ağı, izleme pikseli veya analiz betiği bulunmaz.',
                    ],
                ],
                [
                    'heading' => 'Verinin korunması',
                    'list'    => [
                        'Form verileri, sorguları hazırlanmış ifadelerle çalışan bir veritabanında tutulur.',
                        'Yönetim paneli parola ile korunur; giriş sonrasında oturum kimliği yenilenir.',
                        'Sunucu, tarayıcıya içerik güvenliği politikası (CSP) ve çerçeveleme engeli gibi koruyucu başlıklar gönderir.',
                    ],
                ],
                [
                    'heading' => 'İletişim',
                    'body'    => [
                        'Gizlilikle ilgili sorularınız için: ' . (string) ($brand['phone'] ?? '') . ' · ' . (string) ($brand['address'] ?? ''),
                        'Kişisel verilerinize ilişkin kanuni haklarınız ve başvuru yolu için KVKK Aydınlatma Metni sayfasına bakabilirsiniz.',
                    ],
                ],
            ],
        ];
    }
}
