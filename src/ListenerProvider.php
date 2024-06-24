<?php

declare(strict_types=1);

namespace PHPOMG;

use PHPOMG\Facade\App;
use PHPOMG\Facade\Config;
use PHPOMG\Facade\Framework;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    public function getListenersForEvent(object $event): iterable
    {
        foreach (Config::get('listen', []) as $key => $value) {
            if (is_a($event, $key)) {
                yield function ($event) use ($key, $value) {
                    Framework::execute($value, [
                        $key => $event,
                    ]);
                };
            }
        }
        foreach (App::all() as $appname) {
            foreach (Config::get('listen@' . $appname, []) as $key => $value) {
                if (is_a($event, $key)) {
                    yield function ($event) use ($key, $value) {
                        Framework::execute($value, [
                            $key => $event,
                        ]);
                    };
                }
            }
        }
    }
}
