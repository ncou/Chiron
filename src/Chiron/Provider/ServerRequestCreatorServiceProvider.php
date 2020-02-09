<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Chiron\Container\BindingInterface;

/**
 * Chiron server request creator services provider.
 */
class ServerRequestCreatorServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(BindingInterface $container): void
    {
        /*
        $container[ServerRequestCreatorInterface::class] = function ($c) {
            $requestCreator = new ServerRequestCreator(
                $c[ServerRequestFactoryInterface::class],
                $c[UriFactoryInterface::class],
                $c[UploadedFileFactoryInterface::class],
                $c[StreamFactoryInterface::class]);

            return $requestCreator->fromGlobals();
        };*/


/*
        $container->add(ServerRequestCreatorInterface::class, function ($container) {


            $serverRequestFactory = $container->get(ServerRequestFactoryInterface::class);
            $uriFactory = $container->get(UriFactoryInterface::class);
            $uploadedFileFactory = $container->get(UploadedFileFactoryInterface::class);
            $streamFactory = $container->get(StreamFactoryInterface::class);

            $requestCreator = new ServerRequestCreator(
                $serverRequestFactory,
                $uriFactory,
                $uploadedFileFactory,
                $streamFactory);

            return $requestCreator->fromGlobals();
        });
*/

        $container->add(ServerRequestCreatorInterface::class, ServerRequestCreator::class);



        // *** register alias ***
        $container->alias(ServerRequestCreator::class, ServerRequestCreatorInterface::class);
        //$container->alias('request', ServerRequestCreatorInterface::class);


        /*
        $container[ServerRequestCreator::class] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };

        $container['request'] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };*/
    }
}
