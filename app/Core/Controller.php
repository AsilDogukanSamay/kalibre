<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(
        string $template,
        array $data = [],
        int $status = 200,
        ?string $layout = 'layouts/main'
    ): void {
        Response::html(View::render($template, $data, $layout), $status);
    }

    /** Denetleyici icinden 404 dondurmek icin (rota var, kaynak yok). */
    protected function notFound(): void
    {
        $this->view('errors/404', [], 404);
    }
}
