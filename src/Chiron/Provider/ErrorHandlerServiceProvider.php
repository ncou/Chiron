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

namespace Chiron\Provider;

use Chiron\Handler\Error\ExceptionHandler;
use Chiron\Handler\Error\ExceptionInfo;
use Chiron\Handler\Error\ExceptionManager;
use Chiron\Handler\Error\Formatter\Filter\CanFormatFilter;
use Chiron\Handler\Error\Formatter\Filter\ContentTypeFilter;
use Chiron\Handler\Error\Formatter\Filter\VerboseFilter;
use Chiron\Handler\Error\Formatter\JsonFormatter;
use Chiron\Handler\Error\Formatter\TemplateHtmlFormatter;
use Chiron\Handler\Error\Formatter\ViewFormatter;
use Chiron\Handler\Error\Formatter\WhoopsFormatter;
use Chiron\Handler\Error\Formatter\XmlFormatter;
use Chiron\Handler\Error\HttpExceptionHandler;
use Chiron\Handler\Error\Reporter\LoggerReporter;
use Chiron\Http\Exception\Client\NotFoundHttpException;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Routing\Router;
use Chiron\Views\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Chiron system services provider.
 *
 * Registers system services for Chiron, such as config manager, middleware router and dispatcher...
 */
class ErrorHandlerServiceProvider
{
    /**
     * Register Chiron system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        $container[ExceptionInfo::class] = function ($c) {
            return new ExceptionInfo(__DIR__ . '/../../../resources/lang/en/errors.json');
        };

        $container[ExceptionInfo::class] = function ($c) {
            return new ExceptionInfo(__DIR__ . '/../../../resources/lang/en/errors.json');
        };

        $container[TemplateHtmlFormatter::class] = function ($c) {
            $path = __DIR__ . '/../../../resources/error.html';

            return new TemplateHtmlFormatter($c[ExceptionInfo::class], realpath($path));
        };

        $container[LoggerReporter::class] = function ($c) {
            return new LoggerReporter($c[LoggerInterface::class]);
        };

        $container[ContentTypeFilter::class] = function ($c) {
            return new ContentTypeFilter();
        };
        $container[CanFormatFilter::class] = function ($c) {
            return new CanFormatFilter();
        };
        $container[VerboseFilter::class] = function ($c) {
            return new VerboseFilter($c['debug']);
        };

        $container[ExceptionHandler::class] = function ($c) {
            $exceptionHandler = new ExceptionHandler($c['debug']);

            $exceptionHandler->addReporter($c[LoggerReporter::class]);

            $exceptionHandler->addFormatter(new WhoopsFormatter());

            $hasRenderer = $c->has(TemplateRendererInterface::class);
            if ($hasRenderer) {
                $renderer = $c[TemplateRendererInterface::class];
                registerErrorViewPaths($renderer);
                //$renderer->addPath(\Chiron\TEMPLATES_DIR . "/errors", 'errors');
                $exceptionHandler->addFormatter(new ViewFormatter($c[ExceptionInfo::class], $renderer));
            }

            $exceptionHandler->addFormatter($c[TemplateHtmlFormatter::class]);
            $exceptionHandler->addFormatter(new JsonFormatter($c[ExceptionInfo::class]));
            $exceptionHandler->addFormatter(new XmlFormatter($c[ExceptionInfo::class]));

            $exceptionHandler->setDefaultFormatter($c[TemplateHtmlFormatter::class]);

            return $exceptionHandler;
        };

        $container[HttpExceptionHandler::class] = function ($c) {
            $exceptionHandler = new HttpExceptionHandler($c['debug']);

            $exceptionHandler->addReporter($c[LoggerReporter::class]);

            $exceptionHandler->addFormatter(new WhoopsFormatter());

            $hasRenderer = $c->has(TemplateRendererInterface::class);
            if ($hasRenderer) {
                $renderer = $c[TemplateRendererInterface::class];
                registerErrorViewPaths($renderer);
                //$renderer->addPath(\Chiron\TEMPLATES_DIR . "/errors", 'errors');
                $exceptionHandler->addFormatter(new ViewFormatter($c[ExceptionInfo::class], $renderer));
            }

            $exceptionHandler->addFormatter($c[TemplateHtmlFormatter::class]);
            $exceptionHandler->addFormatter(new JsonFormatter($c[ExceptionInfo::class]));
            $exceptionHandler->addFormatter(new XmlFormatter($c[ExceptionInfo::class]));

            $exceptionHandler->setDefaultFormatter($c[TemplateHtmlFormatter::class]);

            return $exceptionHandler;
        };

        /**
         * Register all the possible error template namespaced paths.
         */
        // TODO : virer cette fonction et améliorer l'intialisation du répertoire des erreurs pour les templates
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

        $container[ErrorHandlerMiddleware::class] = function ($c) {
            $exceptionManager = new ExceptionManager();

            //$exceptionManager->bindExceptionHandler(Throwable::class, new \Chiron\Handler\Error\WhoopsHandler());

            $exceptionManager->bindExceptionHandler(Throwable::class, $c[ExceptionHandler::class]);
            $exceptionManager->bindExceptionHandler(HttpException::class, $c[HttpExceptionHandler::class]);

            //$exceptionManager->bindExceptionHandler(ServiceUnavailableHttpException::class, new \Chiron\Handler\Error\MaintenanceHandler());
            //$exceptionManager->bindExceptionHandler(NotFoundHttpException::class, new \Chiron\Handler\Error\NotFoundHandler());

            return new ErrorHandlerMiddleware($exceptionManager);
        };
    }
}
