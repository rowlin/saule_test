<?php

declare(strict_types=1);

namespace Core\Interfaces;

interface RenderInterface
{
    public function view(string $view_filename, array $data = []): void;
}
