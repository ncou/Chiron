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
use Chiron\Http\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use Chiron\KernelInterface;

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
        $kernel[ServerRequestCreator::class] = function ($c) {
            return new ServerRequestCreator($c[ServerRequestFactory::class],
                $c[UriFactory::class],
                $c[UploadedFileFactory::class],
                $c[StreamFactory::class]);
        };
    }
}
