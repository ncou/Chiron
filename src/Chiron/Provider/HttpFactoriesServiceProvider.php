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
use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Http\Factory\ResponseFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Chiron\Container\BindingInterface;
use Http\Factory\Psr17FactoryFinder;

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
        $container->add(ResponseFactoryInterface::class, function () {
            $factory = Psr17FactoryFinder::findResponseFactory();
            $headers = []; // TODO : aller rechercher dans la config les headers de base à injecter dans la réponse.

            return new ResponseFactory($factory, $headers);
        });

        $container->add(RequestFactoryInterface::class, Psr17FactoryFinder::findRequestFactory());
        $container->add(ServerRequestFactoryInterface::class, Psr17FactoryFinder::findServerRequestFactory());
        $container->add(UriFactoryInterface::class, Psr17FactoryFinder::findUriFactory());
        $container->add(UploadedFileFactoryInterface::class, Psr17FactoryFinder::findUploadedFileFactory());
        $container->add(StreamFactoryInterface::class, Psr17FactoryFinder::findStreamFactory());

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
