<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;

class Container
{
    private array $instances = [];
    private array $factories = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->factories[$id])) {
            $instance = $this->factories[$id]($this);
        } else {
            $instance = $this->autoWire($id);
        }

        $this->instances[$id] = $instance;
        return $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->factories[$id]) || class_exists($id);
    }

    private function autoWire(string $class): object
    {
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return $ref->newInstance();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                } else {
                    throw new \RuntimeException("Cannot resolve parameter \${$param->getName()} in {$class}");
                }
            } else {
                $params[] = $this->get($type->getName());
            }
        }

        return $ref->newInstanceArgs($params);
    }
}
