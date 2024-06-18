<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Route as PHPOMGRoute;

class Route
{
    public static function getInstance(): PHPOMGRoute
    {
        return Container::get(PHPOMGRoute::class);
    }

    public static function isFound(): bool
    {
        return self::getInstance()->isFound();
    }

    public static function isAllowed(): bool
    {
        return self::getInstance()->isAllowed();
    }

    public static function getHandler(): ?string
    {
        return self::getInstance()->getHandler();
    }

    public static function getParams(): array
    {
        return self::getInstance()->getParams();
    }

    public static function getAppName(): ?string
    {
        return self::getInstance()->getAppName();
    }
}
