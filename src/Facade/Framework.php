<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Framework as PHPOMGFramework;

class Framework
{
    public static function getInstance(): PHPOMGFramework
    {
        return Container::get(PHPOMGFramework::class);
    }

    public static function run()
    {
        self::getInstance()->run();
    }

    public static function execute(callable $callable, array $params = [])
    {
        return self::getInstance()->execute($callable, $params);
    }
}
