<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

//https://github.com/php-services/http-factory-nyholm/blob/master/src/NyholmHttpFactoryServiceProvider.php

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
declare(strict_types=1);

namespace Chiron\Provider;

//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Container\Container;
use Chiron\Http\Factory\ResponseFactory;
use Http\Factory\Psr17FactoryFinder;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Chiron http factories services provider.
 */
class HttpFactoriesServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(BindingInterface $container): void
    {
        // *** register factories ***
        $container->bind(ResponseFactoryInterface::class, function () {
            $factory = Psr17FactoryFinder::findResponseFactory();
            $headers = []; // TODO : aller rechercher dans la classe httpConfig les headers de base à injecter dans la réponse.

            return new ResponseFactory($factory, $headers);
        });

        $container->bind(RequestFactoryInterface::class, Psr17FactoryFinder::findRequestFactory());
        $container->bind(ServerRequestFactoryInterface::class, Psr17FactoryFinder::findServerRequestFactory());
        $container->bind(UriFactoryInterface::class, Psr17FactoryFinder::findUriFactory());
        $container->bind(UploadedFileFactoryInterface::class, Psr17FactoryFinder::findUploadedFileFactory());
        $container->bind(StreamFactoryInterface::class, Psr17FactoryFinder::findStreamFactory());

        // *** register alias ***
        $this->registerAlias($container);
    }

    private function registerAlias(Container $container): void
    {
        $container->alias('responseFactory', ResponseFactoryInterface::class);
        $container->alias('requestFactory', RequestFactoryInterface::class);
        $container->alias('serverRequestFactory', ServerRequestFactoryInterface::class);
        $container->alias('uriFactory', UriFactoryInterface::class);
        $container->alias('uploadedFileFactory', UploadedFileFactoryInterface::class);
        $container->alias('streamFactory', StreamFactoryInterface::class);
    }
}
