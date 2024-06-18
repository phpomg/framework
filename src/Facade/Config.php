<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Config as PHPOMGConfig;

class Config
{
    public static function getInstance(): PHPOMGConfig
    {
        return Container::get(PHPOMGConfig::class);
    }

    public static function get(string $key = '', $default = null)
    {
        return self::getInstance()->get($key, $default);
    }

    public static function set(string $key, $value = null)
    {
        return self::getInstance()->set($key, $value);
    }

    public static function save(string $key, $value)
    {
        return self::getInstance()->save($key, $value);
    }
}
