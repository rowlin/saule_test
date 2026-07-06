<?php

declare(strict_types=1);

namespace Core;

use Core\Interfaces\RenderInterface;

class Render implements RenderInterface
{
    public function view(string $view_filename, array $data = []): void
    {
        $data = (object) $data;
        include_once("Views/layouts/header.html");
        include_once("Views/{$view_filename}.php");
        include_once("Views/layouts/footer.html");
    }
}
