<?php

namespace Core;

use Exceptions\NotFoundViewException;
use Exceptions\ServerErrorViewException;

class Router
{
    private string $controllerClassName;
    private string $method;
    private ?Container $container;
    private const IGNORE_PATHS = ['js', 'css'];

    private const API_ROUTES = [
        'events' => ['Controllers\Api\BetController', 'events'],
        'balance' => ['Controllers\Api\BalanceController', 'show'],
        'rates' => ['Controllers\Api\RateController', 'getAll'],
        'updateRates' => ['Controllers\Api\Admin\AdminController', 'updateRates'],
        'changeCurrency' => ['Controllers\Api\BalanceController', 'changeCurrency'],
        'users' => ['Controllers\Api\Admin\AdminController', 'users'],
        'bets' => ['Controllers\Api\Admin\BetController', 'bets'],
        'myBets' => ['Controllers\Api\BetController', 'myBets'],
        'placeBet' => ['Controllers\Api\BetController', 'placeBet'],
        'settleBet' => ['Controllers\Api\Admin\BetController', 'settleBet'],
        'updateBalance' => ['Controllers\Api\Admin\AdminController', 'updateBalance'],
        'setBalance' => ['Controllers\Api\Admin\AdminController', 'setBalance'],
        'userBalances' => ['Controllers\Api\Admin\AdminController', 'userBalances'],
        'profile' => ['Controllers\Api\ProfileController', 'show'],
        'logs' => ['Controllers\Api\LogController', 'logs'],
        'adminLogs' => ['Controllers\Api\Admin\LogController', 'adminLogs'],
        'addContact' => ['Controllers\Api\ContactController', 'add'],
        'deleteContact' => ['Controllers\Api\ContactController', 'delete'],
        'adminAddContact' => ['Controllers\Api\Admin\ContactController', 'adminAdd'],
        'adminDeleteContact' => ['Controllers\Api\Admin\ContactController', 'adminDelete'],
        'adminUserContacts' => ['Controllers\Api\Admin\ContactController', 'adminUserContacts'],
        'addEvent' => ['Controllers\Api\Admin\EventController', 'addEvent'],
    ];

    public function __construct(string $parameters, ?Container $container = null)
    {
        $this->container = $container;
        $parameters = strtok($parameters, '?');
        $params = explode("/", trim($parameters, "/"), 3);

        if (empty($params[0]) || ($params[0] && !in_array($params[0], self::IGNORE_PATHS))) {
            $methodName = !empty($params[1]) ? $params[1] : 'index';
            $args = isset($params[2]) ? explode("/", trim($params[2], "/")) : [];

            if ($params[0] === 'api' && isset(self::API_ROUTES[$methodName])) {
                [$controllerClass, $controllerMethod] = self::API_ROUTES[$methodName];
                $this->controllerClassName = $controllerClass;
                $this->method = $controllerMethod;
                $controller = $this->createController($controllerClass);

                return match ($_SERVER['REQUEST_METHOD']) {
                    'GET', 'DELETE' => $this->makeGetRequest($controller, $args),
                    'POST', 'PATCH', 'PUT' => $this->makePostRequest($controller, $args),
                };
            }

            $controllerName = !empty($params[0]) ? $params[0] : 'Main';
            $this->setController($controllerName)->setMethod($methodName);
            $controller = $this->createController($this->controllerClassName);

            return match ($_SERVER['REQUEST_METHOD']) {
                'GET', 'DELETE' => $this->makeGetRequest($controller, $args),
                'POST', 'PATCH', 'PUT' => $this->makePostRequest($controller, $args),
            };
        } elseif (isset($params[1])) {
            $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . "/Views/{$params[0]}/{$params[1]}";

            if (file_exists($path)) {
                $mime = match ($params[0]) {
                    'js' => 'application/javascript',
                    'css' => 'text/css',
                    default => 'text/plain',
                };
                header("Content-Type: $mime");
                echo file_get_contents($path);
                return;
            }

            $path2 = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . "/Views/{$params[0]}/{$params[1]}.php";
            if (file_exists($path2)) {
                require $path2;
                return;
            }
        }
        throw new ServerErrorViewException("Path not found", 500);
    }

    private function createController(string $class): Controller
    {
        if ($this->container && $this->container->has($class)) {
            $controller = $this->container->get($class);
        } else {
            $controller = new $class();
        }
        if ($this->container) {
            $controller->setContainer($this->container);
        }
        return $controller;
    }

    private function makeGetRequest(Controller $controller, array $args): void
    {
        $controller->{$this->method}($args);
    }

    private function makePostRequest(Controller $controller, array $args): void
    {
        header('Content-Type: application/json');

        $data = $_POST;
        if (empty($data)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $data = $input ?? [];
        }

        if (!empty($data)) {
            $dtoClass = $this->resolveDtoClass();
            if ($dtoClass && class_exists($dtoClass)) {
                $dto = new $dtoClass(...$data);
                $result = empty($args)
                    ? $controller->{$this->method}($dto)
                    : $controller->{$this->method}($args, $dto);
                if ($result !== null) {
                    echo json_encode($result);
                }
                return;
            }

            $result = empty($args)
                ? $controller->{$this->method}($data)
                : $controller->{$this->method}($args, $data);
            if ($result !== null) {
                echo json_encode($result);
            }
            return;
        }

        $result = $controller->{$this->method}();
        if ($result !== null) {
            echo json_encode($result);
        }
    }

    private function resolveDtoClass(): ?string
    {
        $parts = preg_split('/(?=[A-Z])/', $this->method);
        $className = implode('', array_map('ucfirst', $parts));
        $dtoClass = "Dto\\{$className}Dto";
        return class_exists($dtoClass) ? $dtoClass : null;
    }

    private function setController(string $parameter): self
    {
        $controller = ucwords($parameter);
        $controllerClassName = "Controllers\\{$controller}Controller";
        if (class_exists($controllerClassName)) {
            $this->controllerClassName = $controllerClassName;
            return $this;
        }
        throw new NotFoundViewException("Undefined {$controller}", 404);
    }

    private function setMethod(string $parameter): self
    {
        $method = lcfirst(str_replace('-', '', ucwords($parameter, '-')));
        if (method_exists($this->controllerClassName, $method)) {
            $this->method = $method;
            return $this;
        }
        throw new ServerErrorViewException("Undefined method {$parameter} in {$this->controllerClassName}", 500);
    }
}
