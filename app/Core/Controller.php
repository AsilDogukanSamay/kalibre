<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(string $template, array $data = []): void
    {
        Response::html(View::render($template, $data));
    }
}
