<?php

/**
 * Chiron (http://www.chironframework.com).
 *
 * @see      https://github.com/ncou/Chiron
 *
 * @license   https://github.com/ncou/Chiron/blob/master/licenses/LICENSE.md (MIT License)
 */

declare(strict_types=1);

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php

//https://github.com/laracasts/Favorite-This-Demo/blob/master/vendor/filp/whoops/src/Whoops/Provider/Silex/WhoopsServiceProvider.php

//https://github.com/rougin/slytherin/blob/b712b88af8f3dcd24c2814f7b28c9abb5a5919c3/src/Debug/ErrorHandlerIntegration.php

//https://github.com/narrowspark/framework/blob/ccda2dca0c312dbea08814d1372c1802920ebcca/src/Viserio/Component/Exception/Provider/HttpExceptionServiceProvider.php

namespace Chiron\Provider;

use Chiron\Container\Container;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Handler\ErrorHandler;
use Chiron\Handler\Formatter\HtmlFormatter;
use Chiron\Handler\Formatter\JsonFormatter;
use Chiron\Handler\Formatter\PlainTextFormatter;
use Chiron\Handler\Formatter\ViewFormatter;
use Chiron\Handler\Formatter\WhoopsFormatter;
use Chiron\Handler\Formatter\XmlFormatter;
use Chiron\Handler\Reporter\LoggerReporter;
use Chiron\Http\Exception\Client\NotFoundHttpException;
use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Chiron\Container\BindingInterface;
use Chiron\Handler\ErrorManager;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Invoker\Support\Invokable;
use Closure;

/**
 * Chiron error handler services provider.
 */
class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(BindingInterface $container): void
    {
        // TODO : améliorer le cas du html avec une erreur 404, le lien javascript pour revenir à la page d'accueil ne fonctionne pas bien si on a un basePath différent de "/"
        $container->add(HtmlFormatter::class, function () {
            $path = __DIR__ . '/../../../resources/error.html';

            return new HtmlFormatter(realpath($path));
        });

        $container->add(ErrorHandler::class, function ($container) {
            // TODO : aller chercher la responsefactory directement dans le container plutot que de faire un new ResponseFactory !!!!
            $errorHandler = new ErrorHandler($container->get('responseFactory'));

            //$errorHandler->addReporter($container->get(LoggerReporter::class));

            $errorHandler->addFormatter(new WhoopsFormatter());

            $hasRenderer = $container->has(TemplateRendererInterface::class);
            // TODO : en plus du has il faut vérifier si il est bien de l'instance TamplateRendererInterface pour rentrer dans le if !!!!
            if ($hasRenderer) {
                $renderer = $container->get(TemplateRendererInterface::class);
                //registerErrorViewPaths($renderer);
                //$renderer->addPath(\Chiron\TEMPLATES_DIR . "/errors", 'errors');
                $errorHandler->addFormatter(new ViewFormatter($renderer));
            }

            $errorHandler->addFormatter($container->get(HtmlFormatter::class));
            $errorHandler->addFormatter(new JsonFormatter());
            $errorHandler->addFormatter(new XmlFormatter());
            $errorHandler->addFormatter(new PlainTextFormatter());

            //$errorHandler->setDefaultFormatter($c[HtmlFormatter::class]);
            $errorHandler->setDefaultFormatter(new PlainTextFormatter());

            return $errorHandler;
        });






        // TODO : à virer c'est un test
/*
        $container->add(ErrorHandler::class, function ($container) {
            $errorHandler = new ErrorHandler($container->get('responseFactory'));

            $errorHandler->setDefaultFormatter(new PlainTextFormatter());

            return $errorHandler;
        });*/



        /*
         * Register all the possible error template namespaced paths.
         */
        // TODO : virer cette fonction et améliorer l'intialisation du répertoire des erreurs pour les templates
        //https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Exceptions/Handler.php#L391
        //https://laravel-news.com/laravel-5-5-error-views
        /*
        function registerErrorViewPaths(TemplateRendererInterface $renderer)
        {
            $paths = $renderer->getPaths();

            // add all possible folders for errors based on the presents paths
            foreach ($paths as $path) {
                $renderer->addPath($path . '/errors', 'errors');
            }
            // at the end of the stack and in last resort we add the framework error template folder
            $renderer->addPath(__DIR__ . '/../../resources/errors', 'errors');
        }
        */

        $container->share(ErrorManager::class, new Invokable(Closure::fromCallable([$this, 'errorManager'])));
    }

    // TODO : éventuellement séparer cette méthode en deux parties, une pour enregistrer la classe et la seconde pour configurer la partie "bindHandler" ce sera plus propre
    private function errorManager(EnvironmentInterface $env, ErrorHandler $handler, LoggerInterface $logger): ErrorManager
    {
        //$manager = new ErrorManager($container->get('config')->app['debug']);
        //$manager = new ErrorManager($container->get('config')->get('app.debug'));

        //$manager = new ErrorManager(true);

        $manager = new ErrorManager($env->get('APP_DEBUG', false));

        //$manager->bindHandler(Throwable::class, new \Chiron\Exception\WhoopsHandler());

        //$manager->bindHandler(Throwable::class, $container->get(ErrorHandler::class));
        $manager->bindHandler(Throwable::class, $handler);

        //$manager->bindHandler(ServiceUnavailableHttpException::class, new \Chiron\Exception\MaintenanceHandler());
        //$manager->bindHandler(NotFoundHttpException::class, new \Chiron\Exception\NotFoundHandler());

        $manager->setLogger($logger);

        return $manager;
    }
}
