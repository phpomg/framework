<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use Psr\Log\LogLevel;
use PHPOMG\Psr3\DelegatingLogger;
use Stringable;

class Log
{
    public static function getInstance(): DelegatingLogger
    {
        return Container::get(DelegatingLogger::class);
    }

    public static function emergency(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }

    public static function alert(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::ALERT, $message, $context);
    }

    public static function critical(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::CRITICAL, $message, $context);
    }

    public static function error(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::ERROR, $message, $context);
    }

    public static function warning(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::WARNING, $message, $context);
    }

    public static function notice(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::NOTICE, $message, $context);
    }

    public static function info(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::INFO, $message, $context);
    }

    public static function debug(string|Stringable $message, array $context = []): void
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }

    public static function log($level, string|Stringable $message, array $context = []): void
    {
        self::getInstance()->log($level, $message, $context);
    }
}
