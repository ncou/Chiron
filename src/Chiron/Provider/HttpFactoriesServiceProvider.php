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
use Chiron\Http\Factory\RequestFactory;
use Chiron\Http\Factory\ResponseFactory;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Factory\StreamFactory;
use Chiron\Http\Factory\UploadedFileFactory;
use Chiron\Http\Factory\UriFactory;
use Chiron\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Chiron\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Chiron http factories services provider.
 */
class HttpFactoriesServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(Container $container): void
    {
        // *** register factories ***
        $container->add(RequestFactoryInterface::class, function () {
            return new RequestFactory();
        });

        $container->add(ResponseFactoryInterface::class,function () {
            return new ResponseFactory();
        });

        $container->add(ServerRequestFactoryInterface::class, function () {
            return new ServerRequestFactory();
        });

        $container->add(UriFactoryInterface::class, function () {
            return new UriFactory();
        });

        $container->add(UploadedFileFactoryInterface::class, function () {
            return new UploadedFileFactory();
        });

        $container->add(StreamFactoryInterface::class, function () {
            return new StreamFactory();
        });

        // *** register alias ***
        $container->alias(RequestFactory::class, RequestFactoryInterface::class);
        $container->alias(ResponseFactory::class, ResponseFactoryInterface::class);
        $container->alias(ServerRequestFactory::class, ServerRequestFactoryInterface::class);
        $container->alias(UriFactory::class, UriFactoryInterface::class);
        $container->alias(UploadedFileFactory::class, UploadedFileFactoryInterface::class);
        $container->alias(StreamFactory::class, StreamFactoryInterface::class);

        $container->alias('requestFactory', RequestFactoryInterface::class);
        $container->alias('responseFactory', ResponseFactoryInterface::class);
        $container->alias('serverRequestFactory', ServerRequestFactoryInterface::class);
        $container->alias('uriFactory', UriFactoryInterface::class);
        $container->alias('uploadedFileFactory', UploadedFileFactoryInterface::class);
        $container->alias('streamFactory', StreamFactoryInterface::class);

/*
        $kernel[RequestFactory::class] = function ($c) {
            return $c->get(RequestFactoryInterface::class);
        };

        $kernel[ResponseFactory::class] = function ($c) {
            return $c->get(ResponseFactoryInterface::class);
        };

        $kernel[ServerRequestFactory::class] = function ($c) {
            return $c->get(ServerRequestFactoryInterface::class);
        };

        $kernel[UriFactory::class] = function ($c) {
            return $c->get(UriFactoryInterface::class);
        };

        $kernel[UploadedFileFactory::class] = function ($c) {
            return $c->get(UploadedFileFactoryInterface::class);
        };

        $kernel[StreamFactory::class] = function ($c) {
            return $c->get(StreamFactoryInterface::class);
        };*/
    }
}
