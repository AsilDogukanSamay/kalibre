<?php
declare(strict_types=1);

/**
 * Tek giris noktasi (front controller).
 * Web sunucusunun kok dizini public/ olmalidir; app/, .env ve database/
 * web'den erisilebilir degildir.
 */

use App\Core\Autoloader;
use App\Core\FileServer;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

$root = dirname(__DIR__);

require $root . '/app/Core/Autoloader.php';
Autoloader::register($root . '/app');

// Sablon yardimcilari (e, partial, asset) global alanda tanimlanir.
require $root . '/app/Core/helpers.php';

Env::load($root . '/.env');

if (Env::bool('APP_DEBUG')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Europe/Istanbul'));

// Statik varliklar: Range destekli sunucu.
// Uretimde Apache/nginx bunlari zaten kendi sunar ve buraya hic ugramaz;
// bu satir yalnizca "php -S" ile calisirken videonun sarilabilir olmasi icin.
$istekYolu = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($istekYolu, '/assets/')) {
    if (FileServer::tryServe(__DIR__, $istekYolu)) {
        return;
    }
    // Bulunamadi: dahili sunucu kendi karar versin (yonlendirici betigi sozlesmesi)
    return false;
}

$router = new Router();
$router->get('/', 'HomeController@index');
$router->get('/case', 'CaseStudyController@index');
$router->post('/api/contact', 'ContactController@store');

// Yasal metinler. Yol tablosu App\Support\LegalContent::pages() ile eslesir.
$router->get('/kvkk', 'LegalController@show');
$router->get('/gizlilik', 'LegalController@show');

// Arama motoru dosyalari. Statik dosya degil, rota: icerideki mutlak
// adresler .env'deki APP_URL'den uretilir, tek kaynak korunur.
$router->get('/robots.txt', 'SeoController@robots');
$router->get('/sitemap.xml', 'SeoController@sitemap');

// Yonetim paneli. Oturum yalnizca bu rotalarda acilir; ziyaretci
// tarafinda hicbir cerez olusmaz.
$router->get('/yonetim', 'AdminController@index');
$router->get('/yonetim/giris', 'AdminController@loginForm');
$router->post('/yonetim/giris', 'AdminController@login');
$router->post('/yonetim/cikis', 'AdminController@logout');
$router->post('/yonetim/durum', 'AdminController@updateStatus');

$request = Request::capture();

try {
    $router->dispatch($request);
} catch (Throwable $e) {
    // Teknik detay yalnizca gunluge yazilir; ziyaretciye genel mesaj doner.
    error_log('[kernel] ' . $e->getMessage());

    if ($request->expectsJson()) {
        Response::fail('Sunucu hatası.', [], 500);
    } else {
        Response::html(View::render('errors/500'), 500);
    }
}
