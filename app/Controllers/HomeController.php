<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Env;
use App\Core\Request;
use App\Support\Brand;
use App\Support\SiteContent;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('home', SiteContent::all() + [
            'appName'        => Env::get('APP_NAME', 'Bosch Car Service'),
        ]);
    }
}
