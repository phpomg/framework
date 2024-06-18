<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Router\Router as RouterRouter;

class Router
{
    public static function getInstance(): RouterRouter
    {
        return Container::get(RouterRouter::class);
    }

    public static function addGroup(string $prefix, callable $callback, array $params = []): RouterRouter
    {
        return self::getInstance()->addGroup($prefix, $callback, $params);
    }

    public static function addRoute(
        string $route,
        string $handler,
        string $name = null,
        array $methods = ['*'],
        array $params = [],
    ): RouterRouter {
        return self::getInstance()->addRoute($route, $handler, $name, $methods, $params);
    }

    public static function dispatch(string $httpMethod, string $uri): array
    {
        return self::getInstance()->dispatch($httpMethod, $uri);
    }

    public static function build(string $name, array $querys = [], string $methods = 'GET'): string
    {
        return self::getInstance()->build($name, $querys, $methods);
    }

    public static function getData(): array
    {
        return self::getInstance()->getData();
    }
}
