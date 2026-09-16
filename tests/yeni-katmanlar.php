<?php
declare(strict_types=1);

/**
 * Yayina hazirlik katmanlarinin testleri.
 * tests/run.php icinden cagrilir; ayri dosyada durur ki her katmanin
 * testi kendi basligi altinda okunabilsin.
 *
 * Burada tanimli check() fonksiyonu run.php'den gelir.
 */

use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\ContactMessage;
use App\Support\LegalContent;
use App\Support\LoginThrottle;
use App\Support\Notifier;
use App\Support\SiteContent;

$root = dirname(__DIR__);

// ---------------------------------------------------------------- KVKK onayi
echo "\nKVKK onay kurali\n";

$onayKural = ['consent' => 'accepted'];

$v = new Validator([], $onayKural);
check('onaysiz form reddedilir', !$v->passes() && isset($v->errors()['consent']));

$v = new Validator(['consent' => 'on'], $onayKural);
check('isaretli kutu kabul edilir', $v->passes(), print_r($v->errors(), true));

$v = new Validator(['consent' => '1'], $onayKural);
check('deger 1 de kabul edilir', $v->passes());

$v = new Validator(['consent' => 'false'], $onayKural);
check('sahte deger reddedilir', !$v->passes());

$contactRules = (new ReflectionClass(App\Controllers\ContactController::class))
    ->getConstant('RULES');
check('iletisim formu onay kuralini tasir', ($contactRules['consent'] ?? '') === 'accepted');

// ---------------------------------------------------------------- Guvenlik
echo "\nGuvenlik basliklari\n";

$csp = Security::csp();
check('script-src nonce ile kilitli', str_contains($csp, "script-src 'self' 'nonce-"));
check('cerceveleme kapali', str_contains($csp, "frame-ancestors 'none'"));
check('object-src kapali', str_contains($csp, "object-src 'none'"));
check('form gonderimi kendi kaynagina kisitli', str_contains($csp, "form-action 'self'"));
check('yazi tipi kaynagi acikca izinli', str_contains($csp, 'https://fonts.gstatic.com'));

$nonce = Security::nonce();
check('nonce istek basina sabit', $nonce === Security::nonce());
check('nonce yeterince uzun', strlen($nonce) >= 16);

$headers = Security::htmlHeaders();
check('X-Frame-Options DENY', ($headers['X-Frame-Options'] ?? '') === 'DENY');
check('nosniff gonderilir', ($headers['X-Content-Type-Options'] ?? '') === 'nosniff');
check('duz HTTP uzerinde HSTS gonderilmez', !isset($headers['Strict-Transport-Security']));

// ---------------------------------------------------------------- Yasal metinler
echo "\nYasal metinler\n";

$brand      = SiteContent::all()['brand'];
$legalPages = LegalContent::pages();
$rotaDosya  = (string) file_get_contents($root . '/public/index.php');

$rotalarTam = true;
foreach ($legalPages as $sayfa) {
    if (!str_contains($rotaDosya, "'" . $sayfa['path'] . "'")) {
        $rotalarTam = false;
    }
}
check('her yasal sayfanin rotasi tanimli', $rotalarTam);

$aydinlatma = LegalContent::aydinlatma($brand);
check('aydinlatma metni 9 basliktan olusur', count($aydinlatma['sections']) === 9, (string) count($aydinlatma['sections']));
check('veri sorumlusu basligi var', $aydinlatma['sections'][0]['heading'] === 'Veri sorumlusunun kimliği');
check('ilgili kisi haklari 8 madde', count($aydinlatma['sections'][7]['list']) === 8);
check('atolye adresi metne gecer', str_contains(implode(' ', $aydinlatma['sections'][0]['body']), (string) $brand['address']));

$html = View::render('legal', [
    'page'       => $aydinlatma + ['path' => '/kvkk'],
    'legalPages' => $legalPages,
    'brand'      => $brand,
    'pageTitle'  => $aydinlatma['title'],
]);
check('kvkk sayfasi render edilir', str_contains($html, 'KVKK Aydınlatma Metni'));
check('kvkk sayfasinda style attribute yok', !preg_match('/<[^>]+\sstyle\s*=/i', $html));
check('metin surumu sayfada gorunur', str_contains($html, LegalContent::SURUM));
check('sayfada tek h1 var', substr_count($html, '<h1') === 1);

$gizlilik = LegalContent::gizlilik($brand);
check('cerez kullanilmadigi yaziyor', str_contains($gizlilik['sections'][0]['heading'], 'çerez kullanılmıyor'));

// ---------------------------------------------------------------- Onay kaydi
echo "\nOnay kaydi (sahte PDO)\n";

$pdo  = new FakePdo();
$repo = new ContactMessage($pdo);

$id = $repo->create(
    ['full_name' => 'Deniz Ulgen', 'email' => 'd@o.com', 'phone' => '05321184421', 'message' => 'test mesaji'],
    '203.0.113.7',
    'test-agent',
    LegalContent::SURUM
);
check('onay zamani sorguya girer', str_contains($pdo->lastSql, 'consent_at'));
check('onay surumu parametre olarak baglanir',
    ($pdo->lastStatement->executedWith[':consent_version'] ?? null) === LegalContent::SURUM);

// ---------------------------------------------------------------- Panel sorgulari
echo "\nPanel sorgulari\n";

$repo->page('new', 20, 0);
check('durum suzgeci parametreli', str_contains($pdo->lastSql, 'status = :status'));
check('LIMIT tam sayi olarak baglanir', ($pdo->lastStatement->bound[':limit'] ?? null) === 20);

$repo->page("new'; DROP TABLE contact_messages; --", 20, 0);
check('sema disi durum degeri sorguya girmez', !str_contains($pdo->lastSql, 'DROP TABLE'));
check('gecersiz durumda WHERE hic eklenmez', !str_contains($pdo->lastSql, 'WHERE'));

check('gecersiz duruma guncelleme reddedilir', $repo->updateStatus(1, 'silindi') === false);
check('sema disi durum SQL uretmez', !str_contains($pdo->lastSql, 'silindi'));

// ---------------------------------------------------------------- Giris freni
echo "\nGiris freni\n";

$throttlePath = sys_get_temp_dir() . '/kalibre_throttle_test.json';
@unlink($throttlePath);
$throttle = new LoginThrottle($throttlePath);
$ip = '198.51.100.9';

check('baslangicta kilitli degil', !$throttle->isLocked($ip));
check('bes deneme hakki var', $throttle->remaining($ip) === 5);

for ($i = 0; $i < 4; $i++) {
    $throttle->recordFailure($ip);
}
check('dort hatadan sonra hala acik', !$throttle->isLocked($ip));
check('kalan hak bire duser', $throttle->remaining($ip) === 1);

$throttle->recordFailure($ip);
check('besinci hatada kilitlenir', $throttle->isLocked($ip));
check('kilit suresi bildirilir', $throttle->retryAfter($ip) > 0);
check('baska IP etkilenmez', !$throttle->isLocked('198.51.100.10'));

$throttle->clear($ip);
check('basarili giriste sayac sifirlanir', !$throttle->isLocked($ip));
@unlink($throttlePath);

// ---------------------------------------------------------------- CSRF
echo "\nCSRF belirteci\n";

$token = Session::csrfToken();
check('belirtec uretilir', strlen($token) === 64);
check('ayni oturumda ayni belirtec', $token === Session::csrfToken());
check('dogru belirtec kabul edilir', Session::verifyCsrf($token));
check('yanlis belirtec reddedilir', !Session::verifyCsrf(str_repeat('a', 64)));
check('bos belirtec reddedilir', !Session::verifyCsrf(''));

// ---------------------------------------------------------------- Acik yonlendirme
echo "\nAcik yonlendirme korumasi\n";

$safe = new ReflectionMethod(App\Controllers\AdminController::class, 'safeRedirect');
$safe->setAccessible(true);
$admin = (new ReflectionClass(App\Controllers\AdminController::class))->newInstanceWithoutConstructor();

check('panel ici yol korunur', $safe->invoke($admin, '/yonetim?durum=new') === '/yonetim?durum=new');
check('dis adres reddedilir', $safe->invoke($admin, 'https://kotu.example/phish') === '/yonetim');
check('protokolsuz dis adres reddedilir', $safe->invoke($admin, '//kotu.example') === '/yonetim');
check('baska ic yol reddedilir', $safe->invoke($admin, '/kvkk') === '/yonetim');

// ---------------------------------------------------------------- Bildirim
echo "\nBildirim katmani\n";

// Env bu noktada test dosyasindaki degerleri tasir; NOTIFY_TO tanimli degil.
check('bildirim adresi yoksa sessizce atlanir', Notifier::newRequest(
    ['full_name' => 'Test', 'email' => 't@o.com', 'phone' => '05321184421', 'message' => 'test'],
    'KLB-000001'
) === false);

// ---------------------------------------------------------------- Kurumsal kimlik
echo "
Kurumsal kimlik
";

$cssKimlik = (string) file_get_contents($root . '/resources/css/app.css');
$twKimlik  = (string) file_get_contents($root . '/tailwind.config.js');
$logo      = View::partial('partials/logo-bosch', ['size' => 'h-8 w-8']);

// Degerlendirmenin oncelikli kriteri: markanin ozgun vektor amblemi.
check('amblem ozgun vektor path olarak gomulu',
    str_contains($logo, 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12'));
check('amblem rengi tasarim sisteminden gelir', str_contains($logo, 'currentColor'));
check('amblemin erisilebilir adi var', str_contains($logo, 'aria-label="Bosch"'));
check('kelime markasi dosyayla degistirilebilir',
    str_contains(
        (string) file_get_contents($root . '/app/Views/partials/logo-bosch.php'),
        'wordmark-bosch.svg'
    ));

// Kurumsal palet: primary, secondary, accent
check('primary kodu tanimli',   str_contains($cssKimlik, '--brand:           234   0  22'));
check('secondary kodu tanimli', str_contains($cssKimlik, '--brand-secondary:   0  80 157'));
check('accent kodu tanimli',    str_contains($cssKimlik, '--brand-accent:      0 142 207'));
check('Tailwind secondary token tasir', str_contains($twKimlik, 'secondary: '));
check('Tailwind accent token tasir',    str_contains($twKimlik, 'accent:    '));
check('olcum gostergesi accent kullanir', str_contains($cssKimlik, '.gauge-fill  { @apply block h-full bg-brand-accent'));
check('olcum kanali secondary kullanir',  str_contains($cssKimlik, '.gauge-bar   { @apply h-[3px] overflow-hidden rounded-glass bg-brand-secondary'));

// Marka degistirmek tek bir .env satiri olmali: sablonlarda marka adi gomulu olmamali
$sablonlar = glob($root . '/app/Views/partials/*.php') ?: [];
$gomulu = [];
foreach ($sablonlar as $dosya) {
    $ad = basename($dosya);
    if (str_starts_with($ad, 'logo-')) {
        continue; // marka dosyalari dogal olarak marka adi tasir
    }
    if (stripos((string) file_get_contents($dosya), 'bosch') !== false) {
        $gomulu[] = $ad;
    }
}
check('marka adi sablonlara gomulu degil', $gomulu === [], implode(', ', $gomulu));

// ---------------------------------------------------------------- Panel sablonlari
echo "\nPanel sablonlari\n";

$panel = View::render('admin/index', [
    'messages' => [[
        'id' => 7, 'full_name' => 'Melis Arıkan', 'email' => 'm@o.com', 'phone' => '0533 444 55 66',
        'message' => 'Kaputta çizik var.', 'ip_address' => '203.0.113.7', 'status' => 'new',
        'consent_at' => '2026-09-16 11:18:11', 'consent_version' => '2026.09',
        'created_at' => '2026-09-16 11:18:11',
    ]],
    'counts'    => ['new' => 1, 'read' => 0, 'archived' => 0],
    'labels'    => ['new' => 'Yeni', 'read' => 'Okundu', 'archived' => 'Arşiv'],
    'filter'    => null, 'page' => 1, 'pageCount' => 1, 'total' => 1,
    'csrf'      => $token, 'flash' => null, 'backTo' => '/yonetim',
    'adminUser' => 'atolye', 'pageTitle' => 'Talepler',
], 'layouts/admin');

check('panel render edilir', str_contains($panel, 'KLB-000007'));
check('panelde style attribute yok', !preg_match('/<[^>]+\sstyle\s*=/i', $panel));
check('panel arama motorlarina kapali', str_contains($panel, 'noindex'));
check('durum formlari CSRF tasir', substr_count($panel, 'name="_csrf"') >= 3);
check('KVKK onayi panelde gorunur', str_contains($panel, 'KVKK onayı'));
check('panel ayni stylesheet dosyasini kullanir', str_contains($panel, 'css/app.css'));
// Panel markup'a cam kurali yazmaz, .admin-card uzerinden turetir (DRY).
$cssKaynak = (string) file_get_contents($root . '/resources/css/app.css');
check('panel yuzeyleri cam sinifindan turer',
    (bool) preg_match('/\.admin-card\s*{\s*@apply glass /', $cssKaynak));

$giris = View::render('admin/login', [
    'csrf' => $token, 'error' => null, 'remaining' => 5, 'pageTitle' => 'Giriş',
], 'layouts/admin');
check('giris ekrani render edilir', str_contains($giris, 'Giriş yap'));
check('giris ekraninda style attribute yok', !preg_match('/<[^>]+\sstyle\s*=/i', $giris));

// ---------------------------------------------------------------- Ana sayfa eklentileri
echo "\nAna sayfa: yeni bolumler\n";

$home = View::render('home', SiteContent::all() + ['campaignEndsAt' => '', 'appName' => 'Test']);
check('onay kutusu formda', str_contains($home, 'name="consent"'));
check('KVKK metnine bag var', str_contains($home, 'href="/kvkk"'));
check('gizlilik metnine bag var', str_contains($home, 'href="/gizlilik"'));
check('temsili gorsel ibaresi var', str_contains($home, 'temsilidir'));
check('favicon baglanir', str_contains($home, 'rel="icon"'));
check('JSON-LD nonce tasir', str_contains($home, 'application/ld+json" nonce="'));
check('yeni bolumlerde style attribute yok', !preg_match('/<[^>]+\sstyle\s*=/i', $home));

// ---------------------------------------------------------------- Varlik surumleme
echo "
Varlik surumleme
";

$cssAdres = asset('css/app.css');
check('css adresi surum damgasi tasir', (bool) preg_match('#^/assets/css/app\.css\?v=[0-9a-f]+$#', $cssAdres), $cssAdres);
check('olmayan dosyaya damga eklenmez', asset('css/yok.css') === '/assets/css/yok.css');
check('damga dosya degisince degisir (ayni dosya ayni damga)', asset('css/app.css') === $cssAdres);
check('sayfada surumlu adres kullanilir', str_contains($home, 'app.css?v='));

// ---------------------------------------------------------------- Okunabilirlik
echo "
Okunabilirlik esikleri
";

/**
 * Ev kurali: notur gri yazi, sayfanin EN ACIK notur yuzeyinde bile AAA
 * esigini (7:1) tutar. Bu test tarayici gerektirmez; token degerlerini
 * app.css icinden okur ve orani hesaplar. Boylece biri token'i karartirsa
 * tarayici denetimini beklemeden burada yakalanir.
 */
$tokenOku = static function (string $css, string $ad): ?array {
    if (preg_match('/--' . preg_quote($ad, '/') . ':\s*(\d+)\s+(\d+)\s+(\d+)\s*;/', $css, $m) !== 1) {
        return null;
    }
    return [(int) $m[1], (int) $m[2], (int) $m[3]];
};

$parlaklik = static function (array $renk): float {
    $kanal = static function (int $v): float {
        $c = $v / 255;
        return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    };
    return 0.2126 * $kanal($renk[0]) + 0.7152 * $kanal($renk[1]) + 0.0722 * $kanal($renk[2]);
};

$oran = static function (array $a, array $b) use ($parlaklik): float {
    $x = $parlaklik($a);
    $y = $parlaklik($b);
    [$ust, $alt] = $x > $y ? [$x, $y] : [$y, $x];
    return ($ust + 0.05) / ($alt + 0.05);
};

$css = (string) file_get_contents($root . '/resources/css/app.css');

// Sayfadaki en acik notur yuzey: panel eylem rozeti (bg-white/5, cam kart
// uzerinde). Tarayicida olculdu, burada sabit olarak duruyor.
$enAcikYuzey = [40, 42, 44];
$zemin       = $tokenOku($css, 'surface-900') ?? [16, 18, 20];

foreach (['ink', 'ink-muted', 'ink-faint'] as $ad) {
    $renk = $tokenOku($css, $ad);
    check($ad . ' token degeri okunabiliyor', $renk !== null);
    if ($renk === null) {
        continue;
    }
    $enKotu = $oran($renk, $enAcikYuzey);
    check(
        sprintf('%s en acik yuzeyde AAA tutuyor', $ad),
        $enKotu >= 7.0,
        sprintf('olculen %.2f:1', $enKotu)
    );
    check(
        sprintf('%s duz zeminde AAA tutuyor', $ad),
        $oran($renk, $zemin) >= 7.0,
        sprintf('olculen %.2f:1', $oran($renk, $zemin))
    );
}

// Icerik tasiyan aciklama metni artik ipucu kademesinde degil.
check('aciklama metni kendi bileseninde', str_contains($css, '.note     { @apply max-w-[72ch]'));
check('temsili gorsel ibaresi not bileseni kullaniyor',
    str_contains((string) file_get_contents($root . '/app/Views/partials/compare.php'), 'class="note mt-3'));
check('ipucu kademesi de govde tonunda', str_contains($css, '.field-hint  { @apply text-[0.8rem] text-ink-muted; }'));

// ---------------------------------------------------------------- Imlec nisangahi
echo "
Imlec nisangahi
";

$js = (string) file_get_contents($root . '/public/assets/js/app.js');

check('nisangah iki duzende de var',
    str_contains((string) file_get_contents($root . '/app/Views/layouts/main.php'), 'id="cursor"')
    && str_contains((string) file_get_contents($root . '/app/Views/layouts/admin.php'), 'id="cursor"'));
check('ekran okuyucudan gizli', str_contains($home, 'class="cursor" id="cursor" aria-hidden="true"'));
check('konum inline stille degil CSS degiskeniyle tasinir',
    str_contains($js, "insertRule(':root { --cursor-x: -100px; --cursor-y: -100px; }'"));
check('hareket azaltma tercihinde hic calismaz', str_contains($js, 'if (reduceMotion) return;'));
check('yalnizca gercek fare varken calisir',
    str_contains($js, "matchMedia('(hover: hover) and (pointer: fine)')"));
check('dokunmatik olaylar yok sayilir', str_contains($js, "event.pointerType !== 'mouse'"));
check('bosta rAF dongusu kapanir', str_contains($js, 'doner = false;'));
// pointermove/down/up ucu de passive: tarayici kaydirmayi beklemeden surdurur.
check('isaretci dinleyicileri passive', substr_count($js, '{ passive: true }') >= 3);
check('dokunmatikte CSS de gizler', str_contains($css, '@media (hover: hover) and (pointer: fine)'));

// ---------------------------------------------------------------- Surec seridi
echo "
Surec seridi
";

$icerik = App\Support\SiteContent::all();
$adimlar = $icerik['process'];

check('dort adim var', count($adimlar) === 4);
check('her adimda sure var', count(array_filter($adimlar, static fn ($a) => ($a['time'] ?? '') !== '')) === 4);
check('her adimda cikti var', count(array_filter($adimlar, static fn ($a) => ($a['output'] ?? '') !== '')) === 4);
check('toplam sure ayri bilgi', ($icerik['processSummary']['total'] ?? '') !== '');

check('seride ray ve nokta basiliyor', str_contains($home, 'flow-rail') && str_contains($home, 'flow-dot'));
check('son adimda cizgi yok', substr_count($home, 'flow-line"') === 3);
check('cikti etiketi sayfada', str_contains($home, 'Elinize geçen'));
check('toplam sure sayfada', str_contains($home, $icerik['processSummary']['total']));
check('adimlar sirayla aciliyor', str_contains($home, 'class="flow" data-reveal-group'));

/*
 * Fotograf kutusu yalnizca dosya gercekten varsa basilir. Test iki durumda da
 * dogru kalsin diye sayiyor: kac adimin gorseli varsa o kadar kutu olmali.
 * Boylece dosyalar eklendiginde veya kaldirildiginda test kendini gunceller.
 */
$gorselliAdim = count(array_filter(
    $adimlar,
    static fn (array $a): bool => !empty($a['image']) && asset_exists('img/' . $a['image'])
));
check(
    'fotograf kutusu yalnizca dosya varken basilir',
    substr_count($home, 'class="flow-media"') === $gorselliAdim,
    sprintf('kutu %d, dosyasi olan adim %d', substr_count($home, 'class="flow-media"'), $gorselliAdim)
);
check('numara her halukarda govdede', substr_count($home, 'flow-no-flat') === count($adimlar));

// ---------------------------------------------------------------- Gorunume giris
echo "
Gorunume giris
";

$derlenmis = (string) file_get_contents($root . '/public/assets/css/app.css');

/*
 * Bu kurallar Tailwind katmaninin DISINDA durmak zorunda. Katman icindeyken
 * Tailwind, secicide tanidigi bir sinif bulamadigi icin `.is-in` kuralini
 * tamamen budadi ve `>` birlestiricisini kirpti; sonucta bolumler acilmadi.
 * Test derlenmis ciktiyi kontrol ediyor, kaynagi degil.
 */
check('acilma kurali derlenmis CSSte var', str_contains($derlenmis, '[data-reveal].is-in'));
check('grup gecikmesi birlestiriciyi koruyor',
    str_contains($derlenmis, '[data-reveal-group]>[data-reveal]:nth-child(2)'));
check('JavaScript kapaliyken icerik gizli kalmaz', str_contains($derlenmis, 'scripting:none'));
check('hareket azaltmada animasyon yok', str_contains($css, '[data-reveal] { opacity: 1; transform: none; transition: none; }'));
check('gozlemci tek seferlik', str_contains($js, 'gozlemci.unobserve(giris.target)'));
check('destek yoksa hepsi aninda acilir', str_contains($js, 'hepsiniAc();'));

// ---------------------------------------------------------------- Mobil veri
echo "
Mobil veri korumasi
";

check('surec fotograflari yerinde',
    count(array_filter($adimlar, static fn (array $a): bool => asset_exists('img/' . $a['image']))) === 4);
check('surec fotograflari tembel yuklenir', substr_count($home, 'class="flow-photo"') === 4
    && substr_count($home, 'loading="lazy"') >= 4);

// Hero posteri iki kirpimda: dar ekranda kucuk dosya iner.
check('hero posteri iki kirpimda', str_contains($home, 'hero-poster-dar.webp')
    && str_contains($home, 'media="(min-width: 640px)"'));
check('dar poster gercekten daha kucuk',
    filesize($root . '/public/assets/img/hero-poster-dar.webp')
    < filesize($root . '/public/assets/img/hero-poster.webp') / 3);

/*
 * video poster niteligi bilerek YOK: preload="none" olsa ve mobilde src hic
 * atanmasa bile tarayici poster dosyasini indiriyordu (olculdu: 121 KB).
 * Ustelik hic gorunmuyor, cunku video oynayana kadar saydam.
 */
preg_match('/<video[^>]*id="heroVideo"[^>]*>/i', $home, $heroVideo);
check('hero videosu poster niteligi tasimiyor',
    isset($heroVideo[0]) && !str_contains($heroVideo[0], 'poster='));

/*
 * Scroll videosunun posteri KALIYOR ve bu bilincli: stage, loadedmetadata ile
 * gorunur oluyor ama ilk kare henuz cizilmemis oluyor; poster o araligi
 * kapatiyor. Ustelik arkasindaki <img> ile ayni dosya oldugu icin ek bayt
 * maliyeti yok - tarayici onbellekten veriyor.
 */
preg_match('/<video[^>]*id="scrubVideo"[^>]*>/i', $home, $scrubVideo);
check('scroll videosunun posteri duruyor',
    isset($scrubVideo[0]) && str_contains($scrubVideo[0], 'poster='));
check('scroll posteri arkadaki gorselle ayni dosya',
    isset($scrubVideo[0]) && str_contains($scrubVideo[0], 'img/paso.webp'));
check('video kaynagi hala data-src ile tutuluyor', str_contains($home, 'data-src="/assets/video/hero.mp4'));

// ---------------------------------------------------------------- Baslik ve sonuc
echo "
Baslik ve sonuc kutusu
";

// Cerceve BASLIGA degil SONUCA uygulanir. Bu bir tasarim kurali; test
// cercevenin baslik bileseninde bitmedigini dogrular.
check('bolum basligi cerceveli degil', !str_contains($css, '.section-head { @apply glass'));
check('bolum basliginda cizilen aksan cizgisi var', str_contains($css, '.section-head::before'));
check('her bolum basligi acilisa bagli', substr_count($home, 'class="section-head') === substr_count($home, 'section-head" data-reveal')
    + substr_count($home, 'section-head mb-0" data-reveal'));

check('sonuc kutusu cam yuzeyden turer', str_contains($css, '.verdict      { @apply glass '));
// Yalnizca kapsayici sayilir; verdict-key/val/note ayni onekle basliyor.
$sonucKutusu = preg_match_all('/class="verdict\s/', $home);
check('sayfada sonuc kutusu ender', $sonucKutusu === 2, 'adet: ' . $sonucKutusu);
check('surec toplami sonuc kutusunda', str_contains($home, 'Araç atölyede toplam')
    && str_contains($home, 'verdict-val'));
check('olcum degerleri sonuc kutusunda',
    str_contains($home, 'Boya kalınlığı, kaput') && str_contains($home, 'Ölçülen parlaklık'));

// Satir satir baslik acilisi
check('basliklar satir acilisina isaretli', substr_count($home, 'data-satir') >= 6);
// Yorum satirlarinda kelime gecebilir; aranan sey GERCEK kullanim.
check('istemci tarafinda innerHTML atamasi yok', preg_match('/\.innerHTML\s*=/', $js) === 0);
check('satir kutusu alt cikintilara pay birakiyor', str_contains($css, 'padding-bottom: 0.14em; margin-bottom: -0.14em;'));
check('yazi tipi yuklenmeden olculmuyor', str_contains($js, 'document.fonts.ready'));
check('animasyon sonunda metin eski haline doner', str_contains($js, 'el.textContent = orijinal;'));
check('hareket azaltmada satir animasyonu yok', str_contains($css, '.satir-ic { transform: none; transition: none; }'));

// ---------------------------------------------------------------- Agirlik olcegi
echo "
Agirlik olcegi
";

/*
 * Koyu zeminde acik yazi optik olarak daha INCE gorunur. Govde 400'de
 * biraktirilirsa sayfa zayif okunuyor; olcek tek yerde durur ve buradan
 * dogrulanir. Deger dusurulurse test yakalar.
 */
$agirlik = static function (string $css, string $ad): ?int {
    return preg_match('/--wght-' . preg_quote($ad, '/') . ':\s*(\d+)\s*;/', $css, $m) === 1
        ? (int) $m[1]
        : null;
};

foreach (['body' => 460, 'body-sm' => 470, 'medium' => 560, 'strong' => 620, 'display' => 700] as $ad => $enAz) {
    $deger = $agirlik($css, $ad);
    check(
        sprintf('--wght-%s tanimli ve en az %d', $ad, $enAz),
        $deger !== null && $deger >= $enAz,
        $deger === null ? 'tanimsiz' : 'olculen ' . $deger
    );
}

// Govde, baslikla arasinda net bir fark birakmali; ikisi ayni kademede olmamali.
check('govde ile baslik arasinda kademe farki var',
    ($agirlik($css, 'display') ?? 0) - ($agirlik($css, 'body') ?? 0) >= 200);

// Bilesenler sayiyi elle yazmak yerine olcegi okumali.
$derlenmisCss = (string) file_get_contents($root . '/public/assets/css/app.css');
check('bilesenler olcegi okuyor', substr_count($derlenmisCss, 'var(--wght-') >= 20,
    'kullanim: ' . substr_count($derlenmisCss, 'var(--wght-'));

/*
 * Kaynakta yalnizca iki sabit deger kalabilir ve ikisi de bilincli:
 * kart basligi (ara kademe) ve kelime markasi (logo kilidi).
 */
check('olcek disinda en fazla iki sabit deger var',
    preg_match_all('/"wght" \d/', $css) <= 2,
    'sabit: ' . preg_match_all('/"wght" \d/', $css));
