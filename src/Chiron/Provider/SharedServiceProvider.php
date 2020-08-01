<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
declare(strict_types=1);

namespace Chiron\Provider;

//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Application;
use Chiron\Bootload\ServiceProvider\AbstractServiceProvider;
use Chiron\Config\Configure;
use Chiron\Console\Console;
use Chiron\Http\Emitter\EmitterInterface;
use Chiron\Http\Emitter\SapiEmitter;
use Chiron\Http\Http;
use Chiron\Pipe\HttpDecorator;
use Chiron\PublishableCollection;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
final class SharedServiceProvider extends AbstractServiceProvider
{
    protected const SINGLETONS = [
        //Application::class,
        Console::class,
        PublishableCollection::class,
        //Http::class,
        //Configure::class,
        //Directories
        //Environment
        //RouteCollector

        // save some memory
        //HttpDecorator::class,
        //\Nyholm\Psr7Server\ServerRequestCreatorInterface::class,
        //EmitterInterface::class => SapiEmitter::class,
    ];

    /*
    protected const BINDINGS = [
        CookieQueue::class => [self::class, 'cookieQueue']
    ];*/

    /*
    protected const SINGLETONS = [
        ViewsInterface::class => ViewManager::class,
    ];*/

    /*
    protected const ALIASES = [
        'view.manager' => ViewManager::class,
    ];*/
}
