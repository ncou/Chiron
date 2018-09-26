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

namespace Chiron\Provider;

use Chiron\Http\Middleware\BodyParserMiddleware;
use Chiron\Http\Middleware\CharsetByDefaultMiddleware;
use Chiron\Http\Middleware\CheckMaintenanceMiddleware;
use Chiron\Http\Middleware\ContentLengthMiddleware;
use Chiron\Http\Middleware\ContentTypeByDefaultMiddleware;
use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\EmitterMiddleware;
//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Middleware\MethodOverrideMiddleware;
use Chiron\Http\Middleware\OriginalRequestMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Http\ServerRequestCreator;
use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Factory\UriFactory;
use Chiron\Http\Factory\UploadedFileFactory;
use Chiron\Http\Factory\StreamFactory;


/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class ServerRequestCreatorServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        $container[ServerRequestFactoryInterface::class] = function ($c) {

            return new ServerRequestFactory();
        };

        $container[UriFactoryInterface::class] = function ($c) {

            return new UriFactory();
        };

        $container[UploadedFileFactoryInterface::class] = function ($c) {

            return new UploadedFileFactory();
        };

        $container[StreamFactoryInterface::class] = function ($c) {

            return new StreamFactory();
        };


        $container[ServerRequestCreator::class] = function ($c) {

            return new ServerRequestCreator($c[ServerRequestFactoryInterface::class],
                $c[UriFactoryInterface::class],
                $c[UploadedFileFactoryInterface::class],
                $c[StreamFactoryInterface::class]);
        };
    }
}
