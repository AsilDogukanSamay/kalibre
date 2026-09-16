<?php
declare(strict_types=1);

/**
 * Bagimlilik gerektirmeyen duman testleri.
 *   php tests/run.php
 *
 * MySQL olmadan da calisir: veri erisim katmani sahte bir PDO ile test edilir,
 * boylece prepared statement kullanildigi ve degerlerin SQL metnine
 * birlestirilmedigi dogrulanir.
 */

use App\Core\Autoloader;
use App\Core\Validator;
use App\Core\View;
use App\Models\ContactMessage;

$root = dirname(__DIR__);
require $root . '/app/Core/Autoloader.php';
Autoloader::register($root . '/app');
require $root . '/app/Core/helpers.php';

// CSRF testleri oturum gerektirir; cikti baslamadan once acilir.
@session_start();

$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        printf("  [ok]   %s\n", $name);
    } else {
        $fail++;
        printf("  [FAIL] %s %s\n", $name, $detail);
    }
}

// ---------------------------------------------------------------- Validator
echo "\nDogrulama katmani\n";

$rules = [
    'full_name' => 'required|min:3|max:120',
    'email'     => 'required|email|max:180',
    'phone'     => 'required|phone|max:32',
    'message'   => 'required|min:10|max:2000',
];

$v = new Validator([], $rules);
check('bos form reddedilir', !$v->passes());
check('dort alan icin de hata uretir', count($v->errors()) === 4);

$v = new Validator([
    'full_name' => 'Deniz Ulgen',
    'email'     => 'gecersiz-eposta',
    'phone'     => '0532 118 44 21',
    'message'   => 'Audi RS6 icin seramik kaplama fiyati ogrenmek istiyorum.',
], $rules);
check('gecersiz e-posta yakalanir', !$v->passes() && isset($v->errors()['email']));
check('gecerli alanlar hata uretmez', !isset($v->errors()['phone']), print_r($v->errors(), true));

$v = new Validator([
    'full_name' => '  Deniz Ulgen  ',
    'email'     => 'deniz@ornek.com',
    'phone'     => '+90 532 118 44 21',
    'message'   => 'Audi RS6 icin seramik kaplama fiyati ogrenmek istiyorum.',
], $rules);
check('gecerli form kabul edilir', $v->passes(), print_r($v->errors(), true));
check('bosluklar kirpilir', $v->validated()['full_name'] === 'Deniz Ulgen');

$v = new Validator(['full_name' => "Ad\x00Soyad", 'email' => 'a@b.com', 'phone' => '05321184421', 'message' => str_repeat('x', 12)], $rules);
$v->passes();
check('kontrol karakterleri temizlenir', !str_contains($v->validated()['full_name'], "\x00"));

$v = new Validator(['full_name' => 'Ad', 'email' => 'a@b.com', 'phone' => '05321184421', 'message' => str_repeat('x', 12)], $rules);
check('min kurali calisir', !$v->passes() && isset($v->errors()['full_name']));

// ---------------------------------------------------------------- Env
echo "
Yapilandirma okuyucu
";

$tmp = sys_get_temp_dir() . '/kalibre_env_test';
file_put_contents($tmp, implode("
", [
    'DUZ=deger',
    'YORUMLU=deger   # bu aciklama degerin parcasi olmamali',
    'TIRNAKLI="icinde # olan deger"',
    '# tamamen yorum satiri',
    'BOSLUKLU =  kirpilmali  ',
]));
(function () use ($tmp) {
    $r = new ReflectionClass(App\Core\Env::class);
    $r->setStaticPropertyValue('loaded', false);
    $r->setStaticPropertyValue('data', []);
    App\Core\Env::load($tmp);
})();
check('duz deger okunur', App\Core\Env::get('DUZ') === 'deger');
check('satir sonu yorumu kirpilir', App\Core\Env::get('YORUMLU') === 'deger', '-> ' . var_export(App\Core\Env::get('YORUMLU'), true));
check('tirnak icindeki # korunur', App\Core\Env::get('TIRNAKLI') === 'icinde # olan deger', '-> ' . var_export(App\Core\Env::get('TIRNAKLI'), true));
check('bosluklar kirpilir', App\Core\Env::get('BOSLUKLU') === 'kirpilmali');
@unlink($tmp);

// ---------------------------------------------------------------- XSS
echo "\nXSS kacisi\n";

$payload = '<script>alert("xss")</script>';
$escaped = View::e($payload);
check('script etiketi kacirilir', !str_contains($escaped, '<script>'));
check('tirnak kacirilir', str_contains(View::e('a"b\'c'), '&quot;') && str_contains(View::e('a"b\'c'), '&#039;'));
check('e() yardimcisi ayni sonucu verir', e($payload) === $escaped);

// ---------------------------------------------------------------- PDO
echo "\nVeri erisim katmani (sahte PDO)\n";

final class FakeStatement extends PDOStatement
{
    public array $executedWith = [];
    public array $bound = [];
    public function execute(?array $params = null): bool
    {
        $this->executedWith = $params ?? [];
        return true;
    }
    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bound[$param] = $value;
        return true;
    }
    public function fetchColumn(int $column = 0): mixed
    {
        return 2;
    }
    /** Panel listesi sorgulari fetchAll kullanir. */
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return [];
    }
    public function rowCount(): int
    {
        return 1;
    }
}

final class FakePdo extends PDO
{
    public string $lastSql = '';
    public ?FakeStatement $lastStatement = null;
    public function __construct()
    {
    }
    public function prepare(string $query, array $options = []): FakeStatement
    {
        $this->lastSql = $query;
        return $this->lastStatement = new FakeStatement();
    }
    public function lastInsertId(?string $name = null): string
    {
        return '4271';
    }
}

$pdo = new FakePdo();
$repo = new ContactMessage($pdo);

$dirty = [
    'full_name' => "Robert'); DROP TABLE contact_messages;--",
    'email'     => 'saldirgan@ornek.com',
    'phone'     => '05321184421',
    'message'   => 'SQL injection denemesi',
];
$id = $repo->create($dirty, '203.0.113.7', 'PHPUnit');

check('INSERT prepared statement ile calisir', str_contains($pdo->lastSql, ':full_name'));
check('kullanici girdisi SQL metnine girmez', !str_contains($pdo->lastSql, 'DROP TABLE'));
check('deger parametre olarak baglanir', ($pdo->lastStatement->executedWith[':full_name'] ?? null) === $dirty['full_name']);
check('lastInsertId dondurulur', $id === 4271);

$repo->recentCountByIp('203.0.113.7', 10);
check('spam freni sorgusu da parametrelidir', str_contains($pdo->lastSql, ':ip') && str_contains($pdo->lastSql, ':minutes'));
check('IP degeri baglanir', ($pdo->lastStatement->bound[':ip'] ?? null) === '203.0.113.7');

// ---------------------------------------------------------------- Range
echo "
Bayt araligi (Range) cozumleyici
";

$coz = function (string $header, int $boyut) {
    $m = new ReflectionMethod(App\Core\FileServer::class, 'araligiCoz');
    $m->setAccessible(true);
    return $m->invoke(null, $header, $boyut);
};

check('aralik istenmemisse tamami',      $coz('', 1000)            === [0, 999]);
check('bytes=0-499',                     $coz('bytes=0-499', 1000) === [0, 499]);
check('bytes=500- (sona kadar)',         $coz('bytes=500-', 1000)  === [500, 999]);
check('bytes=-200 (son 200 bayt)',       $coz('bytes=-200', 1000)  === [800, 999]);
check('dosya sonunu asan bitis kirpilir',$coz('bytes=900-5000', 1000) === [900, 999]);
check('baslangic dosya disinda ise red', $coz('bytes=5000-', 1000) === [null, 0]);
check('ters aralik reddedilir',          $coz('bytes=800-100', 1000) === [null, 0]);
check('bozuk baslik yok sayilir',        $coz('bytes=abc', 1000)   === [0, 999]);

// ---------------------------------------------------------------- Sablonlar
echo "\nSablonlar\n";

$html = View::render('home', App\Support\SiteContent::all() + ['appName' => 'Test', 'campaignEndsAt' => '2027-01-03T23:59:59+03:00']);
check('ana sayfa render edilir', str_contains($html, 'Boyayı ölçerek düzeltiyoruz.'));
check('hicbir etikette style attribute yok', !preg_match('/<[^>]+\sstyle\s*=/i', $html));
check('hero videosu bagli', str_contains($html, 'video/hero.mp4'));
check('scroll videosu bagli', str_contains($html, 'video/paso.mp4'));
/*
 * Cam yuzey dili sayfanin tamaminda gecerli olmali (kural 5).
 *
 * Sayim yalnizca markup'ta gecen 'glass' kelimesine bakamaz: .cell, .plan,
 * .faq-item, .verdict ve .note-box cam yuzeylerden @apply ile TUREYEN
 * bilesenler; markup'ta 'glass' yazmiyor olmasi o yuzeyin cam olmadigi
 * anlamina gelmez. Eski sayim bunlari gormuyordu ve referans kartlari
 * editoryal sutuna donunce esigin altina dustu - halbuki cam yuzey sayisi
 * degil, referanslarin bicimi degismisti.
 *
 * Sinif adi tam eslesme ile aranir: 'cell' arayisi 'cell-photo' ile eslesmez.
 */
$camTureyen = ['glass', 'glass-strong', 'glass-soft', 'glass-card', 'cell', 'plan', 'faq-item', 'verdict', 'note-box'];
$camSayisi  = 0;
foreach ($camTureyen as $sinif) {
    $camSayisi += preg_match_all('/class="[^"]*(?<![-\w])' . preg_quote($sinif, '/') . '(?![-\w])/', $html);
}
check('cam yuzey siniflari kullanilir', $camSayisi > 10, 'sayim: ' . $camSayisi);

require __DIR__ . '/yeni-katmanlar.php';

// ---------------------------------------------------------------- Beyan edilen sayi
echo "
Beyan edilen sayilar
";

/*
 * Vaka calismasi sayfasi "kac test geciyor" diye bir rakam gosteriyor ve bu
 * rakam elle yaziliydi: her yeni testle sessizce eskiyordu (bu turda 202'de
 * kalmisti). Artik testin kendisi dogruluyor - beyan, BU KONTROL DAHIL toplam
 * test sayisina esit olmak zorunda. Rakami guncellemeden yeni test eklemek
 * artik mumkun degil.
 */
$gecenTest = null;
foreach (App\Support\CaseStudy::all()['metrics'] ?? [] as $olcum) {
    if (($olcum['label'] ?? '') === 'Geçen test') {
        $gecenTest = (int) $olcum['value'];
    }
}
$toplamTest = $pass + $fail + 1;   // +1: bu kontrolun kendisi
check('vaka sayfasindaki test sayisi guncel', $gecenTest === $toplamTest,
    'beyan: ' . var_export($gecenTest, true) . ' gercek: ' . $toplamTest);

printf("\n%d gecti, %d kaldi\n\n", $pass, $fail);
exit($fail === 0 ? 0 : 1);
