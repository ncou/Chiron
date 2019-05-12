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

use Chiron\KernelInterface;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

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
    public function register(KernelInterface $kernel): void
    {
        /*
        $kernel[ServerRequestCreatorInterface::class] = function ($c) {
            $requestCreator = new ServerRequestCreator(
                $c[ServerRequestFactoryInterface::class],
                $c[UriFactoryInterface::class],
                $c[UploadedFileFactoryInterface::class],
                $c[StreamFactoryInterface::class]);

            return $requestCreator->fromGlobals();
        };*/


        $kernel->add(ServerRequestCreatorInterface::class, function (ServerRequestFactoryInterface $serverRequestFactory, UriFactoryInterface $uriFactory, UploadedFileFactoryInterface $uploadedFileFactory, StreamFactoryInterface $streamFactory) {
            $requestCreator = new ServerRequestCreator(
                $serverRequestFactory,
                $uriFactory,
                $uploadedFileFactory,
                $streamFactory);

            return $requestCreator->fromGlobals();
        });



        // *** register alias ***
        $kernel->alias(ServerRequestCreator::class, ServerRequestCreatorInterface::class);
        $kernel->alias('request', ServerRequestCreatorInterface::class);
        /*
        $kernel[ServerRequestCreator::class] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };

        $kernel['request'] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };*/
    }
}
