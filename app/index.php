<?php

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/constants.php';

$container = new Core\Container();
$container->set(Core\Interfaces\SessionInterface::class, fn() => new Core\SessionManager());
$container->set(Core\Interfaces\RenderInterface::class, fn() => new Core\Render());
$container->set(Services\Auth::class, fn($c) => new Services\Auth($c->get(Core\Interfaces\SessionInterface::class)));

$route = $_SERVER['REQUEST_URI'] ?? "";
try {
    new Core\Router($route, $container);
} catch (\Exception $e) {
    if ($e instanceof Exceptions\ViewException) {
        return $e->render();
    } else {
        http_response_code($e->getCode() ?: 500);
        echo $e->getMessage();
    }
}
