<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Basit metot + yol eslestiricisi. Denetleyiciler "Sinif@metot" olarak verilir.
 */
final class Router
{
    /** @var array<string,array<string,string>> */
    private array $routes = [];

    public function get(string $path, string $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, string $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function dispatch(Request $request): void
    {
        $action = $this->routes[$request->method][$request->path] ?? null;

        if ($action === null) {
            if ($request->expectsJson()) {
                Response::fail('Kaynak bulunamadı.', [], 404);
                return;
            }
            Response::html(View::render('errors/404'), 404);
            return;
        }

        [$class, $method] = explode('@', $action, 2);
        $fqcn = 'App\Controllers\\' . $class;

        /** @var Controller $controller */
        $controller = new $fqcn();
        $controller->{$method}($request);
    }
}
