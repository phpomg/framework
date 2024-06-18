<?php

declare(strict_types=1);

namespace PHPOMG;

use Closure;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use PHPOMG\Database\Db;
use PHPOMG\Facade\Config;
use PHPOMG\Facade\Container;
use PHPOMG\Facade\Emitter;
use PHPOMG\Facade\Event;
use PHPOMG\Facade\HttpFactory;
use PHPOMG\Facade\Route;
use PHPOMG\Facade\Router;
use PHPOMG\ListenerProvider;
use PHPOMG\Psr3\DelegatingLogger;
use PHPOMG\Psr11\Container as Psr11Container;
use PHPOMG\Psr14\Event as Psr14Event;
use PHPOMG\Psr15\RequestHandler;
use PHPOMG\Psr16\NullAdapter;
use PHPOMG\Psr17\Factory;
use PHPOMG\Request\Request;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Framework
{
    public function __construct()
    {
        $container = Container::getInstance();
        $items = [
            Psr11Container::class => $container,
            ContainerInterface::class => Psr11Container::class,
            LoggerInterface::class => DelegatingLogger::class,
            CacheInterface::class => NullAdapter::class,
            EventDispatcherInterface::class => Psr14Event::class,
            ListenerProviderInterface::class => Psr14Event::class,
            ResponseFactoryInterface::class => Factory::class,
            UriFactoryInterface::class => Factory::class,
            ServerRequestFactoryInterface::class => Factory::class,
            RequestFactoryInterface::class => Factory::class,
            StreamFactoryInterface::class => Factory::class,
            UploadedFileFactoryInterface::class => Factory::class,
            Db::class => [
                'master_config' => Config::get('database.master_config', []),
                'slaves_config' => Config::get('database.slaves_config', []),
            ],
            Event::class => function (
                Event $event,
                ListenerProvider $listenerProvider
            ) {
                $event->addProvider($listenerProvider);
            },
            Request::class => [
                ServerRequestInterface::class => ServerRequest::fromGlobals()->withQueryParams(array_merge($_GET, Route::getParams())),
            ]
        ];

        foreach ($items as $id => $vo) {
            if (is_array($vo)) {
                $container->setArgument($id, $vo);
            } elseif (is_string($vo)) {
                $container->set($id, function () use ($vo, $container) {
                    return $container->get($vo);
                });
            } elseif ($vo instanceof Closure) {
                $container->set($id, $vo);
            } elseif (is_object($vo)) {
                $container->set($id, function () use ($vo) {
                    return $vo;
                });
            } else {
                throw new Exception('the option ' . $id . ' cannot config..');
            }
        }

        foreach (Config::get('bootstrap', []) as $id => $vo) {
            if (is_array($vo)) {
                $container->setArgument($id, $vo);
            } elseif (is_string($vo)) {
                $container->set($id, function () use ($vo, $container) {
                    return $container->get($vo);
                });
            } elseif ($vo instanceof Closure) {
                $container->set($id, $vo);
            } elseif (is_object($vo)) {
                $container->set($id, function () use ($vo) {
                    return $vo;
                });
            } else {
                throw new Exception('the option ' . $id . ' cannot config..');
            }
        }
    }

    public function run()
    {
        Event::dispatch(Container::getInstance());

        Event::dispatch(Router::getInstance());

        if (!Route::isFound()) {
            $response = HttpFactory::createResponse(404);
        } else if (!Route::isAllowed()) {
            $response = HttpFactory::createResponse(405);
        } else {
            $serverRequest = ServerRequest::fromGlobals()->withQueryParams(array_merge($_GET, Route::getParams()));
            $response = (new RequestHandler)->handle($serverRequest);
        }
        Emitter::emit($response);
    }

    public function execute(callable $callable, array $params = [])
    {
        $args = Container::getInstance()->reflectArguments($callable, $params);
        return call_user_func($callable, ...$args);
    }
}
