<?php

declare(strict_types=1);

namespace PHPOMG;

use Composer\Autoload\ClassLoader;
use Exception;
use ReflectionClass;

class App
{
    private $lists = [];

    public function __construct()
    {
        $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
        if (file_exists($root . '/vendor/composer/installed.json')) {
            foreach (json_decode(file_get_contents($root . '/vendor/composer/installed.json'), true)['packages'] as $pkg) {
                if ($pkg['type'] != 'psrapp') {
                    continue;
                }
                if (file_exists($root . '/config/' . $pkg['name'] . '/disabled.lock')) {
                    continue;
                }
                $this->lists[$pkg['name']] = [
                    'dir' => $root . '/vendor/' . $pkg['name'],
                ];
            }
        }

        spl_autoload_register(function (string $class) use ($root) {
            $paths = explode('\\', $class);
            if (isset($paths[3]) && $paths[0] == 'App' && $paths[1] == 'Plugin') {
                $file = $root . '/plugin/'
                    . strtolower(preg_replace('/([A-Z])/', "-$1", lcfirst($paths[2])))
                    . '/src/library/'
                    . str_replace('\\', '/', substr($class, strlen($paths[0]) + strlen($paths[1]) + strlen($paths[2]) + 3))
                    . '.php';
                if (file_exists($file)) {
                    include $file;
                }
            }
        });

        if (is_dir($root . '/plugin')) {
            foreach (scandir($root . '/plugin') as $vo) {
                if (in_array($vo, array('.', '..'))) {
                    continue;
                }
                if (!is_dir($root . '/plugin' . DIRECTORY_SEPARATOR . $vo)) {
                    continue;
                }
                $appname = 'plugin/' . $vo;
                if (file_exists($root . '/config/' . $appname . '/disabled.lock')) {
                    continue;
                }
                if (!file_exists($root . '/config/' . $appname . '/install.lock')) {
                    continue;
                }
                $this->lists[$appname] = [
                    'dir' => $root . '/' . $appname,
                ];
            }
        }
    }

    public function has(string $appname): bool
    {
        return isset($this->lists[$appname]);
    }

    public function add(string $appname, string $dir): self
    {
        $this->lists[$appname] = [
            'dir' => $dir,
        ];
        return $this;
    }

    public function getDir(string $appname): string
    {
        if (!$this->has($appname)) {
            throw new Exception($appname . ' is not found!');
        }
        return $this->lists[$appname]['dir'];
    }

    public function all(): array
    {
        return array_keys($this->lists);
    }
}
