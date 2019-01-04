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

namespace Chiron\Provider;

use Chiron\Handler\ErrorHandler;
use Chiron\Handler\ExceptionManager;
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
use Chiron\KernelInterface;
use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Chiron error handler services provider.
 */
class ErrorHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(KernelInterface $kernel): void
    {
        $kernel[HtmlFormatter::class] = function ($c) {
            $path = __DIR__ . '/../../../resources/error.html';

            return new HtmlFormatter(realpath($path));
        };

        $kernel[LoggerReporter::class] = function ($c) {
            //return new LoggerReporter($c[LoggerInterface::class]);
            return new LoggerReporter($c['logger']);
        };

        $kernel[ErrorHandler::class] = function ($c) {
            $errorHandler = new ErrorHandler($c->config->app['debug']);

            $errorHandler->addReporter($c[LoggerReporter::class]);

            //$errorHandler->addFormatter(new WhoopsFormatter());

            $hasRenderer = $c->has(TemplateRendererInterface::class);
            if ($hasRenderer) {
                $renderer = $c[TemplateRendererInterface::class];
                //registerErrorViewPaths($renderer);
                //$renderer->addPath(\Chiron\TEMPLATES_DIR . "/errors", 'errors');
                $errorHandler->addFormatter(new ViewFormatter($renderer));
            }

            //$errorHandler->addFormatter($c[HtmlFormatter::class]);
            //$errorHandler->addFormatter(new JsonFormatter());
            //$errorHandler->addFormatter(new XmlFormatter());
            $errorHandler->addFormatter(new PlainTextFormatter());

            $errorHandler->setDefaultFormatter($c[HtmlFormatter::class]);

            return $errorHandler;
        };

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

        $kernel[ErrorHandlerMiddleware::class] = function ($c) {
            $exceptionManager = new ExceptionManager();

            //$exceptionManager->bindHandler(Throwable::class, new \Chiron\Exception\WhoopsHandler());

            $exceptionManager->bindHandler(Throwable::class, $c[ErrorHandler::class]);

            //$exceptionManager->bindHandler(ServiceUnavailableHttpException::class, new \Chiron\Exception\MaintenanceHandler());
            //$exceptionManager->bindHandler(NotFoundHttpException::class, new \Chiron\Exception\NotFoundHandler());

            return new ErrorHandlerMiddleware($exceptionManager);
        };
    }
}
