<?php

declare(strict_types=1);

namespace PHPOMG;

use Closure;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use PHPOMG\Database\Db;
use PHPOMG\Facade\App;
use PHPOMG\Facade\Cache;
use PHPOMG\Facade\Config;
use PHPOMG\Facade\Container;
use PHPOMG\Facade\Db as FacadeDb;
use PHPOMG\Facade\Emitter;
use PHPOMG\Facade\Event;
use PHPOMG\Facade\HttpFactory;
use PHPOMG\Facade\Log;
use PHPOMG\Facade\Request as FacadeRequest;
use PHPOMG\Facade\RequestHandler;
use PHPOMG\Facade\Route;
use PHPOMG\Facade\Router;
use PHPOMG\Facade\Session;
use PHPOMG\ListenerProvider;
use PHPOMG\Psr3\DelegatingLogger;
use PHPOMG\Psr11\Container as Psr11Container;
use PHPOMG\Psr14\Event as Psr14Event;
use PHPOMG\Psr15\RequestHandler as Psr15RequestHandler;
use PHPOMG\Psr16\NullAdapter;
use PHPOMG\Psr17\Factory;
use PHPOMG\Request\Request;
use PHPOMG\Template\Template;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Framework
{
    public function __construct()
    {
        $container = Container::getInstance();
        foreach (array_merge([
            LoggerInterface::class => DelegatingLogger::class,
            CacheInterface::class => NullAdapter::class,
            ResponseFactoryInterface::class => Factory::class,
            UriFactoryInterface::class => Factory::class,
            ServerRequestFactoryInterface::class => Factory::class,
            RequestFactoryInterface::class => Factory::class,
            StreamFactoryInterface::class => Factory::class,
            UploadedFileFactoryInterface::class => Factory::class,
            Template::class => function (
                Template $template
            ) {
                $template->setCache(Cache::getInstance());
                $template->addFinder(function (string $tpl): ?string {
                    if (strpos($tpl, '@')) {
                        list($file, $appname) = explode('@', $tpl);
                        if ($appname && $file) {
                            $dir = App::getDir($appname);
                            $fullname = $dir . '/src/template/' . $file . '.php';
                            if (is_file($fullname)) {
                                return file_get_contents($fullname);
                            }
                        }
                    }
                    return null;
                });
                $template->assign([
                    'db' => FacadeDb::getInstance(),
                    'cache' => Cache::getInstance(),
                    'logger' => Log::getInstance(),
                    'router' => Router::getInstance(),
                    'config' => Config::getInstance(),
                    'session' => Session::getInstance(),
                    'request' => FacadeRequest::getInstance(),
                    'template' => $template,
                    'container' => Container::getInstance(),
                ]);
                $template->extend('/\{cache\s*(.*)\s*\}([\s\S]*)\{\/cache\}/Ui', function ($matchs) {
                    $params = array_filter(explode(',', trim($matchs[1])));
                    if (!isset($params[0])) {
                        $params[0] = 3600;
                    }
                    if (!isset($params[1])) {
                        $params[1] = 'tpl_extend_cache_' . md5($matchs[2]);
                    }
                    return '<?php echo call_user_func(function($args){
                        extract($args);
                        if (!$cache->has(\'' . $params[1] . '\')) {
                            $res = $template->renderFromString(base64_decode(\'' . base64_encode($matchs[2]) . '\'), $args, \'__' . $params[1] . '\');
                            $cache->set(\'' . $params[1] . '\', $res, ' . $params[0] . ');
                        }else{
                            $res = $cache->get(\'' . $params[1] . '\');
                        }
                        return $res;
                    }, get_defined_vars());?>';
                });
            },
            Db::class => [
                'master_config' => Config::get('database.master_config', []),
                'slaves_config' => Config::get('database.slaves_config', []),
            ],
            ContainerInterface::class => Psr11Container::class,
            EventDispatcherInterface::class => Psr14Event::class,
            ListenerProviderInterface::class => Psr14Event::class,
            Psr14Event::class => function (
                Psr14Event $event,
                ListenerProvider $listenerProvider
            ) {
                $event->addProvider($listenerProvider);
            },
            Psr15RequestHandler::class => function () {
                if (!Route::isFound()) {
                    $handler = new class implements RequestHandlerInterface
                    {
                        public function handle(ServerRequestInterface $request): ResponseInterface
                        {
                            return HttpFactory::createResponse(404);
                        }
                    };
                } else if (!Route::isAllowed()) {
                    $handler = new class implements RequestHandlerInterface
                    {
                        public function handle(ServerRequestInterface $request): ResponseInterface
                        {
                            return HttpFactory::createResponse(405);
                        }
                    };
                } else {
                    $handler = Container::get(Route::getHandler());
                }
                return new Psr15RequestHandler(Container::getInstance(), $handler);
            },
            Request::class => function () {
                return new Request(ServerRequest::fromGlobals()->withQueryParams(array_merge($_GET, Route::getParams())));
            },
        ], Config::get('container', []), [
            Psr11Container::class => $container,
        ]) as $id => $vo) {
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

        Event::dispatch(App::getInstance());

        Event::dispatch(Router::getInstance());

        Event::dispatch(Route::getInstance());

        $serverRequest = ServerRequest::fromGlobals()->withQueryParams(array_merge($_GET, Route::getParams()));
        $response = RequestHandler::handle($serverRequest);

        Emitter::emit($response);
    }

    public function execute(callable $callable, array $params = [])
    {
        $args = Container::reflectArguments($callable, $params);
        return call_user_func($callable, ...$args);
    }
}
