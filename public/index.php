<?php
declare(strict_types=1);

/**
 * Tek giris noktasi (front controller).
 * Web sunucusunun kok dizini public/ olmalidir; app/, .env ve database/
 * web'den erisilebilir degildir.
 */

use App\Core\Autoloader;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

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

$router = new Router();
$router->get('/', 'HomeController@index');
$router->get('/case', 'CaseStudyController@index');
$router->post('/api/contact', 'ContactController@store');

try {
    $router->dispatch(Request::capture());
} catch (Throwable $e) {
    error_log('[kernel] ' . $e->getMessage());
    Response::fail('Sunucu hatası.', [], 500);
}
