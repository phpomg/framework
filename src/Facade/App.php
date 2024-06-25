<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\App as PHPOMGApp;

class App
{
    public static function getInstance(): PHPOMGApp
    {
        return Container::get(PHPOMGApp::class);
    }

    public static function has(string $appname): bool
    {
        return self::getInstance()->has($appname);
    }

    public static function add(string $appname, string $dir): PHPOMGApp
    {
        return self::getInstance()->add($appname, $dir);
    }

    public static function getDir(string $appname): string
    {
        return self::getInstance()->getDir($appname);
    }

    public static function all(): array
    {
        return self::getInstance()->all();
    }
}
