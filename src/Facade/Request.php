<?php

declare(strict_types=1);

namespace PHPOMG\Facade;

use PHPOMG\Request\Request as RequestRequest;

class Request
{
    public static function getInstance(): RequestRequest
    {
        return Container::get(RequestRequest::class);
    }

    public static function has(string $field): bool
    {
        return self::getInstance()->has($field);
    }

    public static function server(string $field = '', $default = null)
    {
        return self::getInstance()->server($field, $default);
    }

    public static function get(string $field = '', $default = null)
    {
        return self::getInstance()->get($field, $default);
    }

    public static function post(string $field = '', $default = null)
    {
        return self::getInstance()->post($field, $default);
    }

    public static function request(string $field = '', $default = null)
    {
        return self::getInstance()->request($field, $default);
    }

    public static function cookie(string $field = '', $default = null)
    {
        return self::getInstance()->cookie($field, $default);
    }

    public static function file(string $field = '', $default = null)
    {
        return self::getInstance()->file($field, $default);
    }

    public static function attr(string $field = '', $default = null)
    {
        return self::getInstance()->attr($field, $default);
    }

    public static function header(string $field = '', $default = null)
    {
        return self::getInstance()->header($field, $default);
    }
}
