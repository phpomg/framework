<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Psr15\RequestHandler as Psr15RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class RequestHandler
{
    public static function getInstance(): Psr15RequestHandler
    {
        return Container::get(Psr15RequestHandler::class);
    }

    public static function pushMiddleware(...$middlewares)
    {
        return self::getInstance()->pushMiddleware(...$middlewares);
    }

    public static function unShiftMiddleware(...$middlewares)
    {
        return self::getInstance()->unShiftMiddleware(...$middlewares);
    }

    public static function popMiddleware(): ?MiddlewareInterface
    {
        return self::getInstance()->popMiddleware();
    }

    public static function shiftMiddleware(): ?MiddlewareInterface
    {
        return self::getInstance()->shiftMiddleware();
    }

    public static function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return self::getInstance()->handle($serverRequest);
    }
}
