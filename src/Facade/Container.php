<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use Closure;
use PHPOMG\Psr11\Container as Psr11Container;

class Container
{
    public static function getInstance(): Psr11Container
    {
        static $container;
        if ($container == null) {
            $container = new Psr11Container;
        }
        return $container;
    }

    public static function get(string $id, bool $new = false)
    {
        return self::getInstance()->get($id, $new);
    }

    public static function has(string $id): bool
    {
        return self::getInstance()->has($id);
    }

    public static function set(string $id, Closure $fn): Psr11Container
    {
        return self::getInstance()->set($id, $fn);
    }

    public static function setArgument(string $id, array $args): Psr11Container
    {
        return self::getInstance()->setArgument($id, $args);
    }

    public static function reflectArguments($callable, array $default = []): array
    {
        return self::getInstance()->reflectArguments($callable, $default);
    }
}
