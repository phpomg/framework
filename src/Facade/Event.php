<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use Psr\EventDispatcher\ListenerProviderInterface;
use PHPOMG\Psr14\Event as Psr14Event;

class Event
{
    public static function dispatch(object $event)
    {
        return self::getInstance()->dispatch($event);
    }

    public static function addProvider(ListenerProviderInterface $listenerProvider): Psr14Event
    {
        return self::getInstance()->addProvider($listenerProvider);
    }

    public static function getInstance(): Psr14Event
    {
        return Container::get(Psr14Event::class);
    }
}
