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

/*
 * Degerlendirmenin oncelikli kriteri: markanin ozgun vektor logosu.
 * Amblem de kelime markasi da Bosch'un marka rehberinden cikan resmi dosyadan
 * geliyor; kaynak dosya depoda duruyor. Imza olarak path verisinin ayirt edici
 * bir parcasi aranir - bir gun "benzerini cizip" koymak isteyen olursa test
 * tutmaz.
 */
$resmiKaynak = $root . '/public/assets/img/bosch-logo-kaynak.svg';
check('resmi logo dosyasi depoda', is_file($resmiKaynak));

$kaynakSvg = is_file($resmiKaynak) ? (string) file_get_contents($resmiKaynak) : '';
$amblemImza = 'M48.2.18a48.2,48.2,0,1,0,48.2,48.2';
$kelimeImza = 'M185.2,46.88a13.77,13.77,0,0,0,8.8-13';

check('amblem resmi dosyadaki vektorun aynisi',
    str_contains($logo, $amblemImza) && str_contains($kaynakSvg, $amblemImza));
check('kelime markasi da vektor, tipografi degil',
    str_contains($logo, $kelimeImza) && str_contains($kaynakSvg, $kelimeImza));
check('kelime markasi bes harf yolundan olusur', substr_count($logo, 'fill="currentColor"') >= 7);
check('logo rengi tasarim sisteminden gelir', str_contains($logo, 'currentColor'));
check('amblemin erisilebilir adi var', str_contains($logo, 'aria-label="Bosch"'));
check('kaynagi ve lisansi sablonda yazili',
    str_contains((string) file_get_contents($root . '/app/Views/partials/logo-bosch.php'), 'brandguide.bosch.com')
    && str_contains((string) file_get_contents($root . '/app/Views/partials/logo-bosch.php'), 'PD-textlogo'));
check('favicon da ayni resmi amblemi kullanir',
    str_contains((string) file_get_contents($root . '/public/assets/img/favicon-bosch.svg'), $amblemImza));

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

$home = View::render('home', SiteContent::all() + ['appName' => 'Test', 'campaignEndsAt' => '2027-01-03T23:59:59+03:00']);
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
/*
 * Her bolum basligi acilisa bagli olmali: aksan cizgisi .is-in ile ciziliyor,
 * data-reveal yoksa cizgi hic gorunmez. Sinif dizisi bolumden bolume degistigi
 * icin sayarak degil, ETIKETIN kendisine bakarak dogrulanir.
 */
preg_match_all('/<[^>]*class="[^"]*section-head[^"]*"[^>]*>/', $home, $basliklar);
$acilissiz = array_values(array_filter(
    $basliklar[0],
    static fn (string $etiket): bool => !str_contains($etiket, 'data-reveal')
));
check('her bolum basligi acilisa bagli', $acilissiz === [], implode(' | ', $acilissiz));
check('sayfada en az bes bolum basligi var', count($basliklar[0]) >= 5, 'adet: ' . count($basliklar[0]));

check('sonuc kutusu cam yuzeyden turer', str_contains($css, '.verdict      { @apply glass '));
// Yalnizca kapsayici sayilir; verdict-key/val/note ayni onekle basliyor.
$sonucKutusu = preg_match_all('/class="verdict\s/', $home);
check('sayfada sonuc kutusu ender', $sonucKutusu === 2, 'adet: ' . $sonucKutusu);
check('surec toplami sonuc kutusunda', str_contains($home, 'Araç atölyede toplam')
    && str_contains($home, 'verdict-val'));
/*
 * Iki olcum etiketi PARALEL olmali: ikisi de ayni sablonu kullanir
 * (buyukluk, panel). Onceki halde biri "Boya kalinligi, kaput" digeri
 * "Olculen parlaklik" diyordu; ayni kutuda iki farkli dil konusuluyordu.
 */
check('olcum degerleri sonuc kutusunda',
    str_contains($home, 'Boya kalınlığı, kaput') && str_contains($home, 'Parlaklık, kaput'));

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

// ---------------------------------------------------------------- Etkilesim katmani
echo "
Etkilesim katmani
";

// Her bolum ayni dili konusmali: etiket + aksan cizgisi + baslik.
preg_match_all('/<[^>]*class="[^"]*section-head[^"]*"[^>]*>(.*?)<h2/s', $home, $bloklar);
$etiketsiz = 0;
foreach ($bloklar[1] as $blok) {
    if (!str_contains($blok, 'eyebrow-label')) {
        $etiketsiz++;
    }
}
check('her bolum basliginda etiket var', $etiketsiz === 0, 'etiketsiz: ' . $etiketsiz);

// Olcum sayaclari
check('istatistikler sayaca bagli', substr_count($home, 'data-sayac') === 3);
check('sayac hareket azaltmada calismaz',
    (bool) preg_match('/olcumSayaclari[\s\S]{0,600}?if \(reduceMotion/', $js));
check('sayac bicimi Turkce binlik ayraci kullanir', str_contains($js, "toLocaleString('tr-TR')"));

// SSS acilisi
check('sss panelleri sarmalanmis', substr_count($home, 'class="faq-panel"') === 4);
check('sss yuksekligi inline stille yazilmaz',
    str_contains($js, 'insertRule(`[data-faq=') && !preg_match('/panel\.style\.height/', $js));
check('gecis bitmezse zaman asimi devreye girer', str_contains($js, 'window.setTimeout(bir, 500)'));
check('open niteligi animasyon bitince kalkar', str_contains($js, 'oge.open = false;'));

// Hover derinligi ve isik gecisi
check('birincil butonda isik gecisi var', str_contains($css, '.btn-primary::after'));
check('kartlarda hover derinligi var', str_contains($css, '.cell:hover') && str_contains($css, '.plan:hover'));
check('bento fotograflari hoverda yakinlasir', str_contains($css, '.cell:hover .cell-photo'));

// Alt bilgi
check('alt bilgi uc sutuna ayrildi', str_contains($home, 'foot-grid'));
check('telif satiri var', str_contains($home, 'foot-telif') && str_contains($home, date('Y')));

// ---------------------------------------------------------------- Tasarim yukseltmesi
echo "\nTasarim yukseltmesi\n";

/*
 * TONAL BOLUM (chapter)
 * Sayfa 11.000 piksel boyunca tek bir koyulukta akiyordu. Ton artik bolum
 * GRUBUNA uygulaniyor; tek tek her bolume sirayla vermek serit etkisi
 * yapiyordu. Uc bant bekleniyor: teklif, manifesto, eylem.
 */
check('yuzey olcegi ucuncu kademeyi tanimliyor', str_contains($css, '--surface-950'));
check('bolum tonu bilesenleri tanimli',
    str_contains($css, '.band-raise') && str_contains($css, '.band-deep'));
check('sayfa tonal bolumlere ayrilmis',
    substr_count($home, 'class="band-raise"') === 2 && substr_count($home, 'class="band-deep"') === 1,
    'raise: ' . substr_count($home, 'class="band-raise"') . ' deep: ' . substr_count($home, 'class="band-deep"'));

/*
 * KIRMIZI DISIPLINI
 * Onceki halde sayfadaki ON bolumun ust etiketi de marka kirmizisiydi; her
 * yerde olan bir vurgu vurgu olmaktan cikiyordu. Kirmizi artik anlatinin uc
 * durak noktasinda: hero'daki olcum kaniti, fiyat ve randevu.
 */
check('bolum etiketi varsayilan olarak notur',
    (bool) preg_match('/\.eyebrow-label \{[^}]*text-ink-faint/', $css));
check('marka renkli etiket ayri bir degistirici',
    str_contains($css, '.eyebrow-brand'));
check('marka renkli etiket en fazla uc yerde',
    substr_count($home, 'eyebrow-brand') <= 3,
    'sayim: ' . substr_count($home, 'eyebrow-brand'));
check('bolum cizgisi de varsayilan olarak notur',
    (bool) preg_match('/\.section-head::before \{[^}]*bg-line-strong/', $css));

/*
 * KAMPANYA GERI SAYIMI
 * Gorev tanimindaki donusum artirici ogelerden biri. Bir tur kaldirilmisti
 * (indirim sayacinin sayfanin olcum tonuyla celistigi gerekcesiyle), isveren
 * beklentisi dogrultusunda geri kondu. Bes katman da yerinde olmali:
 * sablon, isaretleme, davranis, stil ve yapilandirma.
 */
check('geri sayim sablonu var', is_file($root . '/app/Views/partials/countdown.php'));
check('geri sayim isaretleri var', str_contains($home, 'data-countdown'));
check('geri sayim davranisi var', str_contains($js, 'data-cd'));
check('geri sayim stilleri var', str_contains($css, '.countdown-'));
check('geri sayim yapilandirmasi var',
    str_contains((string) file_get_contents($root . '/.env.example'), 'CAMPAIGN_ENDS_AT'));
// Bitis tarihi bos birakilirsa bolum hic render edilmemeli.
check('bos tarihte geri sayim render edilmez',
    !str_contains(View::render('home', SiteContent::all() + ['appName' => 'Test', 'campaignEndsAt' => '']), 'data-countdown'));
// Suresi dolunca sayac gizlenip bilgilendirmeye doner.
check('suresi dolan kampanya icin bilgilendirme var', str_contains($home, 'data-countdown-expired'));

/*
 * REFERANSLAR: YILDIZ YERINE OLCUM
 * Bes kirmizi yildiz her sitede ayni sekilde duruyor ve hicbir sey olcmuyor.
 * Her referansin altinda artik o araca ait olcum sonucu var.
 */
$referanslar = SiteContent::all()['testimonials'];
check('referanslarda yildiz derecelendirmesi yok',
    !str_contains($home, 'class="star"') && !str_contains($css, '.star {'));
check('her referansta olcum sonucu var',
    count(array_filter($referanslar, static fn ($r) => isset($r['olcum']['value']) && $r['olcum']['value'] !== '')) === count($referanslar));
check('olcumler birbirinden farkli buyuklukler',
    count(array_unique(array_column(array_column($referanslar, 'olcum'), 'key'))) === count($referanslar));
check('referans kart degil editoryal sutun',
    str_contains($home, 'class="referans"') && !str_contains($home, 'referans glass'));
check('referans bolumu kurgu oldugunu soyluyor',
    (bool) preg_match('/referans[\s\S]{0,4000}?temsilidir/u', $home));

/*
 * MANIFESTO
 * Editoryal kesinti: sayfanin kalibina UYMAYAN tek bolum. Etiketi ve karti
 * olmamasi bilincli; testin korudugu sey de bu.
 */
check('manifesto bolumu var', str_contains($home, 'class="manifesto"'));

// Bolumun KENDI govdesini ayiklayip icinde ariyoruz: tum sayfada arayan bir
// desen, manifestodan sonraki bolumlerin etiketlerini de gorup yaniltir.
$manifestoBlok = preg_match('/<section class="manifesto">([\s\S]*?)<\/section>/u', $home, $mEs) ? $mEs[1] : '';
check('manifesto govdesi ayiklanabiliyor', $manifestoBlok !== '');
check('manifesto etiket tasimiyor',
    $manifestoBlok !== '' && !str_contains($manifestoBlok, 'eyebrow-label'));
check('manifesto kart degil',
    $manifestoBlok !== '' && !str_contains($manifestoBlok, 'glass'));
check('manifesto cumlesi satir acilisina bagli',
    (bool) preg_match('/manifesto-quote[^>]*data-satir/', $home));
check('manifesto baslik sesinden farkli konusuyor',
    (bool) preg_match('/\.manifesto-quote \{[\s\S]*?"wdth" 106/', $css));

/*
 * OKUMA ILERLEMESI
 * Sayfanin dili olcum; serit altindaki cizgi de okunan mesafenin olcumu.
 * Rengi olcum skalasindan gelir, marka kirmizisindan degil.
 */
check('okuma ilerlemesi seritte duruyor', str_contains($home, 'class="nav-progress"'));
check('ilerleme degeri CSS degiskeninden okunur',
    str_contains($css, 'var(--okuma-p'));
check('ilerleme degeri inline stille yazilmaz',
    str_contains($js, "insertRule(':root { --okuma-p: 0; }'") && !preg_match('/nav\.style\./', $js));
check('ilerleme cizgisi olcum renginde, marka renginde degil',
    (bool) preg_match('/\.nav-progress \{[^}]*bg-brand-accent/', $css));
check('serit kaydirilinca matlasir', str_contains($js, "'nav-kaydirildi'"));

/*
 * SCROLL'A BAGLI HAREKET
 * JavaScript yok: `animation-timeline: view()`. Desteklemeyen tarayicida
 * hicbir sey degismez, cunku kurallar @supports icinde duruyor.
 */
check('paralaks scroll zaman cizgisiyle calisir',
    str_contains($css, 'animation-timeline: view()'));
check('paralaks destek sorgusuyla korunuyor',
    (bool) preg_match('/@supports \(animation-timeline: view\(\)\)/', $css));
check('paralaks hareket azaltmada calismaz',
    (bool) preg_match('/@supports \(animation-timeline: view\(\)\) \{\s*@media \(prefers-reduced-motion: no-preference\)/', $css));
/*
 * Paralaks `translate` ozelligini kullanir, `transform` degil: `.cell:hover
 * .cell-photo` zaten transform: scale kullaniyor, ayni ozellik olsaydi
 * animasyon hover'i ezerdi.
 */
check('paralaks hover buyutmesini ezmiyor',
    (bool) preg_match('/@keyframes foto-paralaks \{[^}]*translate:/', $css));
check('kaydirma kurallari katman disinda',
    strrpos($css, '.nav-bar.nav-kaydirildi') > strrpos($css, '@layer utilities'));

/*
 * OLU SINIF KALMADI
 * .plan, .plan-best, .plan-price ve .plan-badge CSS'te tanimliydi ama fiyat
 * kartlari onlari hic kullanmiyordu: fiyat rakami sayfanin olcum sesinde
 * degildi ve dort sinif sessizce hicbir sey yapmiyordu.
 */
foreach (['plan', 'plan-best', 'plan-price', 'plan-badge'] as $sinif) {
    check("tanimli sinif kullaniliyor: .$sinif",
        str_contains($css, '.' . $sinif) && str_contains($home, $sinif));
}
check('fiyat rakami olcum sesinde',
    (bool) preg_match('/\.plan-price \{[^}]*readout/', $css));

// Hero: olcum karti
check('hero olcum karti degisim miktarini gosteriyor',
    substr_count($home, 'olcum-delta') === 4,
    'sayim: ' . substr_count($home, 'olcum-delta'));
check('kaydirma isareti hero icinde', str_contains($home, 'class="hero-cue"'));

/*
 * METIN ICI BAGLANTI TEK KAYNAKTAN TURER
 * Ayni utility zinciri dort yerde tekrar ediyordu: iki sablonda ham sinif
 * listesi, CSS'te iki ayri bilesen icinde. Marka renginin alt cizgi
 * yogunlugunu degistirmek dort yerde duzeltme gerektiriyordu.
 */
check('baglanti stili tek yerde tanimli',
    substr_count($css, 'decoration-brand/40 underline-offset-4') === 1,
    'sayim: ' . substr_count($css, 'decoration-brand/40 underline-offset-4'));
check('sablonlarda ham baglanti zinciri yok',
    !str_contains($home, 'decoration-brand/40'));
check('turemis baglanti bilesenleri tek kaynagi okuyor',
    str_contains($css, '.legal-link    { @apply link-ic; }') && str_contains($css, '.foot-harita { @apply link-ic'));

/*
 * BOYA KESITI (manifesto bolumunun sag sutunu)
 *
 * Buraya FOTOGRAF konmadi ve konmamali: manifestonun isi sayfanin kalibina
 * uymamak. Bir atolye karesi onu diger alti bolumden ayirt edilemez yapardi.
 * Yerine cumlenin kaniti duruyor.
 *
 * Cizim OLCEKLI: katman yukseklikleri flex-grow degerlerinden geliyor ve o
 * degerler mikron rakamlarinin kendisi. Test bu iki yerin birbirinden
 * kaymadigini dogrular - sablondaki rakam degisip CSS'teki oran kalirsa
 * cizim yalan soylemeye baslar.
 */
$kesitKatmanlari = ['vernik' => 48, 'boya' => 22, 'astar' => 38, 'ekaplama' => 30];

check('kesit manifesto bolumunun icinde',
    $manifestoBlok !== '' && str_contains($manifestoBlok, 'class="kesit"'));
check('kesit fotograf degil',
    $manifestoBlok !== '' && !str_contains($manifestoBlok, '<img'));

foreach ($kesitKatmanlari as $ad => $mikron) {
    check("katman yuksekligi mikron degerine esit: $ad",
        (bool) preg_match('/\.kesit-' . $ad . '\s*\{[^}]*flex:\s*' . $mikron . ' 1 0%/', $css),
        "beklenen flex-grow: $mikron");
    check("katman degeri sayfada yaziyor: $ad",
        (bool) preg_match('/kesit-deger">' . $mikron . '</', $manifestoBlok));
}

check('katmanlarin toplami beyan edilen film kalinligina esit',
    array_sum($kesitKatmanlari) === 138 && str_contains($manifestoBlok, '>138<'),
    'toplam: ' . array_sum($kesitKatmanlari));

/*
 * Guvenli sinirin konumu 30/48 oraninin kendisi: cizginin altinda kalan
 * 30 mikron dokunulmaz. Yuzde elle yazilmis bir "goze guzel gelen" deger
 * degil, hesabin sonucu.
 */
check('guvenli sinir konumu 30/48 oranina esit',
    (bool) preg_match('/\.kesit-sinir\s*\{[^}]*bottom:\s*62\.5%/', $css)
    && abs(30 / 48 * 100 - 62.5) < 0.001);
check('sokulebilir bolge tenti ayni orana dayaniyor',
    (bool) preg_match('/\.kesit-vernik::before\s*\{[^}]*bottom:\s*62\.5%/', $css));

/*
 * Buyuk harfe cevrim "µm" birimini "MM" yapar (mikro isareti buyuk harfte
 * Yunan Mu'suna doner). Sayfanin dili olcum; yanlis birim gosteren bir etiket
 * kabul edilemez. Bir kez yasandi, test geri gelmesini engelliyor.
 */
check('kesit etiketlerinde buyuk harfe cevrim yok',
    !(bool) preg_match('/\.kesit-(sinir-et|deger|birim-ic)[^{]*\{[^}]*uppercase/', $css));

// Sinir cizgisi bolum goruse girdiginde ciziliyor; kural katman disinda (kural 9).
check('kesit sinir cizgisi acilisa bagli',
    strrpos($css, '.kesit.is-in .kesit-sinir') > strrpos($css, '@layer utilities'));


/*
 * FOTOGRAF DENENDI VE KALDIRILDI
 * Manifestonun arkasina zemin olarak kondu: piksel olcumu geri cevirdi
 * (notur etiket 5,08:1, kural 6 yedi istiyor). Sonra tam genislik serit
 * yapildi: bu kez konu okunmuyordu - krom silindir her kirpimda kadraji
 * aliyor ve "arac boyasi" demiyordu. Sayfada zaten uc guclu gorsel an var;
 * dorduncusu doldurucu olurdu. Gerekce KARARLAR.md 7j'de.
 */
check('manifestoda fotograf yok', $manifestoBlok !== '' && !str_contains($manifestoBlok, '<img'));
check('atolye seridi kalintisi yok',
    !str_contains($home, 'atolye-serit') && !preg_match('/\.atolye-/', $css));
check('yetim gorsel dosyasi kalmadi',
    !is_file($root . '/public/assets/img/atolye-serit.webp')
    && !is_file($root . '/public/assets/img/manifesto-zemin.webp'));

// ---------------------------------------------------------------- Metin
echo "\nMetin\n";

/*
 * TEK BIR FIZIKSEL IDDIA, TEK BIR RAKAM
 * Kesme pasosunun vernikten ne kadar aldigi sayfada UC yerde soyleniyor:
 * scroll bolumunde, SSS cevabinda ve kesit ciziminin alt yazisinda. Uc yer de
 * ayni rakami konusmak zorunda; biri degisip digerleri kalirsa sayfa kendi
 * kendiyle celisir. Once "birkac mikron" ve "2-4 µm" olarak ayrisiyordu.
 */
/*
 * Olcum ham HTML'de degil, OKUYUCUNUN GORDUGU metinde yapilir: sablonlarda
 * &ndash; gibi varliklar var ve ham metinde arayan bir desen ayni cumleyi
 * goremiyor. Bir kez yanlis alarm verdi.
 */
$metin = html_entity_decode(strip_tags($home), ENT_QUOTES | ENT_HTML5, 'UTF-8');

$pasoIddiasi = preg_match_all('/paso başına 2[-–]4/u', $metin);
check('paso basina alinan miktar uc yerde de ayni', $pasoIddiasi === 3,
    'gecis: ' . $pasoIddiasi);
check('kesme pasosu vernikten alir, boyadan degil',
    !preg_match('/kesme pasosu boyadan/iu', $home));

/*
 * OLCULMEMIS IDDIA YOK
 * "En cok tercih edilen" bir populerlik istatistigi iddia ediyordu ve arkasinda
 * olculmus bir sey yoktu. Sayfanin tonu bunu kaldirmaz; kendi onerimiz oldugunu
 * soylemek hem dogru hem ayni isi goruyor.
 */
foreach (['En çok tercih edilen', 'en iyi', 'lider', 'Türkiye’nin', "Türkiye'nin"] as $iddia) {
    check("olculmemis iddia yok: \"$iddia\"", !str_contains($home, $iddia));
}

/*
 * META ACIKLAMASI TEK KAYNAKTAN TURER
 * Ayni cumle hem layouts/main.php hem SiteContent icinde yaziliydi; birini
 * duzeltip digerini unutmak an meselesiydi (nitekim bu turda tam da o oldu).
 */
$duzen = (string) file_get_contents($root . '/app/Views/layouts/main.php');
check('meta aciklamasi sablonda kopyalanmiyor',
    !str_contains($duzen, 'boya düzeltme ve seramik kaplama atölyesi. Her araç'));
check('meta aciklamasi icerik kaynagindan okunur',
    str_contains($duzen, "SiteContent::all()['hero']['subtitle']"));
check('meta aciklamasi hero alt yazisini tasiyor',
    str_contains($home, e(SiteContent::all()['hero']['subtitle'])));

/*
 * KARSILIKSIZ INGILIZCE TERIM YOK
 * Sektor terimleri kalabilir ama ilk gectikleri yerde Turkce karsiligi
 * parantez icinde verilir; musteri ne okudugunu bilmeli.
 */
foreach (['Swirl (yıkama izi)', 'kendi kendini onaran (self healing)', '(ekstraksiyon)'] as $terim) {
    check("terim karsiligiyla birlikte: \"$terim\"", str_contains($home, $terim));
}

/*
 * AYNI VAAT UC KEZ TEKRARLANMASIN
 * "Ayni gun ariyoruz" sozu iletisim bolumunde, form ipucunda ve SSS'te olmak
 * uzere uc yerde geciyordu. Soz bir kez verilir, bir kez hatirlatilir.
 */
$ayniGun = substr_count($metin, 'Aynı gün') + substr_count($metin, 'aynı gün');
check('ayni gun sozu en fazla iki yerde', $ayniGun <= 2, 'gecis: ' . $ayniGun);

/*
 * DENETIMLER TEK BIR ISLETIM SISTEMINE CAKILI OLMAMALI
 *
 * Dort denetim betiginde de Chrome yolunun varsayilani Windows'a cakiliydi.
 * Proje Windows'ta gelistirildigi icin fark edilmiyordu; depoyu klonlayan
 * kisinin Windows kullandigini varsayamayiz. Artik ortak bir cozucu var:
 * CHROME ortam degiskeni, yoksa isletim sisteminin bilinen yollari, hicbiri
 * yoksa ne yapilacagini SOYLEYEN bir hata.
 */
$denetimler = ['yerlesim', 'kontrast', 'hero-kontrast', 'hareket'];
foreach ($denetimler as $betik) {
    $kaynak = (string) file_get_contents($root . '/tests/' . $betik . '.mjs');
    check("denetim isletim sistemi bagimsiz: $betik",
        str_contains($kaynak, 'chromeYolu()') && !str_contains($kaynak, 'C:/Program Files'));
}

$cozucu = (string) file_get_contents($root . '/tests/chrome-yolu.mjs');
foreach (['win32', 'darwin', 'linux'] as $isletim) {
    check("Chrome cozucusu $isletim yollarini biliyor", str_contains($cozucu, $isletim));
}
check('Chrome bulunamazsa ne yapilacagi soyleniyor',
    str_contains($cozucu, 'CHROME="/yol/chrome"'));
