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

use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Nyholm\Psr7Server\ServerRequestCreator;
use Chiron\KernelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Chiron server request creator services provider.
 */
class ServerRequestCreatorServiceProvider extends ServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(KernelInterface $kernel): void
    {
        $kernel[ServerRequestCreatorInterface::class] = function ($c) {
            $requestCreator = new ServerRequestCreator(
                $c[ServerRequestFactoryInterface::class],
                $c[UriFactoryInterface::class],
                $c[UploadedFileFactoryInterface::class],
                $c[StreamFactoryInterface::class]);

            return $requestCreator->fromGlobals();
        };

        // *** register alias ***
        $kernel[ServerRequestCreator::class] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };

        $kernel['request'] = function ($c) {
            return $c->get(ServerRequestCreatorInterface::class);
        };
    }
}
