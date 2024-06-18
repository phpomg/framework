<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

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
}
