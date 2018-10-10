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
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Factory\StreamFactory;
use Chiron\Http\Factory\UploadedFileFactory;
use Chiron\Http\Factory\UriFactory;
use Chiron\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class HttpFactoriesServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        // *** register factories ***
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

        // *** register alias ***
        $container[ServerRequestFactory::class] = function ($c) {
            return $c->get(ServerRequestFactoryInterface::class);
        };

        $container[UriFactory::class] = function ($c) {
            return $c->get(UriFactoryInterface::class);
        };

        $container[UploadedFileFactory::class] = function ($c) {
            return $c->get(UploadedFileFactoryInterface::class);
        };

        $container[StreamFactory::class] = function ($c) {
            return $c->get(StreamFactoryInterface::class);
        };
    }
}
