<?php

declare(strict_types=1);

namespace PHPOMG;

use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use PHPOMG\Facade\App;
use PHPOMG\Facade\Router;
use Psr\Http\Server\RequestHandlerInterface;

class Route
{
    private $found = false;
    private $allowed = false;
    private $handler = null;
    private $params = [];
    private $appname = null;

    public function __construct()
    {
        $uri = ServerRequest::getUriFromGlobals();
        $res = Router::dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', '' . $uri->withQuery(''));

        $this->found = $res[0];

        if ($this->found) {
            $cls = $res[2] ?? null;
            if (!is_string($cls) || !is_subclass_of($cls, RequestHandlerInterface::class)) {
                throw new Exception("路由仅支持"); // todl..
            }
            $this->allowed = $res[1] ?? false;
            $this->handler = $cls;
            $this->params = $res[3] ?? [];
        } else {
            $uri = ServerRequest::getUriFromGlobals();
            $paths = explode('/', $uri->getPath());
            $pathx = explode('/', $_SERVER['SCRIPT_NAME']);
            foreach ($pathx as $key => $value) {
                if (isset($paths[$key]) && ($paths[$key] == $value)) {
                    unset($paths[$key]);
                }
            }
            if (count($paths) >= 3) {
                array_splice($paths, 0, 0, 'App');
                array_splice($paths, 3, 0, 'Http');
                $cls = str_replace(['-'], [''], ucwords(implode('\\', $paths), '\\-'));
                if (is_subclass_of($cls, RequestHandlerInterface::class)) {
                    $this->found = true;
                    $this->allowed = true;
                    $this->handler = $cls;
                    $this->params = [];
                }
            }
        }

        if ($this->found) {
            $paths = explode('\\', $this->handler);
            if (!isset($paths[4]) || $paths[0] != 'App' || $paths[3] != 'Http') {
                $this->found = false;
            }
            $camelToLine = function (string $str): string {
                return strtolower(preg_replace('/([A-Z])/', "-$1", lcfirst($str)));
            };
            $appname = $camelToLine($paths[1]) . '/' . $camelToLine($paths[2]);
            if (!App::has($appname)) {
                $this->found = false;
            }
            $this->appname = $appname;
        }
    }

    public function isFound(): bool
    {
        return $this->found;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getAppName(): ?string
    {
        return $this->appname;
    }
}
