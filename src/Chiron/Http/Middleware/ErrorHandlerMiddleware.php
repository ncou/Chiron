<?php

declare(strict_types=1);

// originalRequest : https://github.com/zendframework/zend-expressive/blob/c6db5b1a7524414eee0637bb50b8eed32fd67794/src/Middleware/WhoopsErrorResponseGenerator.php

// Gérer le cas ou il y a une erreur en interne dans le handler :
//https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php#L138
//https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/ErrorHandler.php#L141
//https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L121

// régle le niveau d'affichage des erreurs :
//******************************************
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php#L28

//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php

//https://github.com/Lansoweb/api-problem/blob/master/src/ErrorResponseGenerator.php   +   https://github.com/Lansoweb/api-problem/blob/master/src/Model/ApiProblem.php

//************************************
// TODO : comment faire des tests du middleware : https://github.com/cakephp/cakephp/blob/master/tests/TestCase/Error/Middleware/ErrorHandlerMiddlewareTest.php
//https://github.com/l0gicgate/Slim/blob/4.x-ErrorMiddleware/tests/Middleware/ErrorMiddlewareTest.php
//************************************

// TODO : regarder ici : https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php

// TODO : attacher des listeners pour permettre de logger les erreurs par exemple !!!!! https://github.com/zendframework/zend-stratigility/blob/master/src/Middleware/ErrorHandler.php#L116    +  https://docs.zendframework.com/zend-stratigility/v3/error-handlers/

//https://github.com/middlewares/error-handler/blob/master/src/ErrorHandler.php

// TODO : regarder ici pour la gestion des logs : https://github.com/juliangut/slim-exception/blob/master/src/ExceptionManager.php#L285

// TODO : regarder ici : https://github.com/zendframework/zend-stratigility/blob/master/src/Middleware/ErrorHandler.php

// TODO : regarder ici : https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php

// TODO : regarder ici comment c'est fait : https://github.com/zendframework/zend-problem-details/blob/master/src/ProblemDetailsMiddleware.php

// TODO : faire un clear output avant d'envoyer la réponse ???? https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/ErrorHandler.php#L138

//-----------------
//https://github.com/samsonasik/ErrorHeroModule/blob/master/src/HeroTrait.php#L22
//https://github.com/samsonasik/ErrorHeroModule/blob/master/src/Middleware/Expressive.php#L59

//********* EXCEPTION MANAGER ****************

// WHOOPS + Template 404...etc
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Exceptions/Handler.php
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Exceptions/WhoopsHandler.php
//https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php
//https://github.com/laravel/framework/tree/master/src/Illuminate/Foundation/Exceptions/views

// Ajouter dans le fichier .env des variable pour gérer les exceptions :
//APP_ENV=dev|prod
//APP_DEBUG=true|false
//APP_KEY=SomeRandomString    <= à utiliser pour le cookie encrypt par exemple
//APP_LOG_LEVEL="debug"

// TODO : regarder ici pour gérer les template en cas d'erreurs (fatfree framework)
//https://github.com/vijinho/f3-boilerplate/blob/3d3f8169bc3a73ccd09c2b45e61dbe5b88b4d845/app/lib/App/App.php

//https://github.com/cakephp/cakephp/blob/master/src/Error/BaseErrorHandler.php#L390
//https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php#L125

namespace Chiron\Http\Middleware;

use Chiron\Handler\ErrorHandlerInterface;
use Chiron\Handler\ExceptionManager;
use Chiron\Http\Psr\Response;
use Chiron\Support\Http\Serializer;
use ErrorException;
//use Psr\Container\ContainerInterface;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public const ORIGINAL_REQUEST = '__originalRequest__';

    /**
     * List of HTTP exception handlers.
     *
     * @var ErrorHandlerInterface[]
     */
    private $handlers = [];

    /**
     * in Debug mode, the error handler should be verbose.
     *
     * @var bool
     */
    private $debug;

    /**
     * @var ExceptionManager
     */
    //private $exceptionManager;

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     */
    //TODO : attention le middleware n'a pas toujours un container de setté automatiquement depuis qu'on a changé le composant RequestHandlerStack !!!!!
    /*
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }*/

    // TODO : ajouter un paramétre displayExceptionDetail = true/false pour afficher ou non le détail de l'exception.
    // TODO : virer ce constructeur et utiliser directement la config dans le container pour voir si on doit afficher le detail de l'exception "displayExceptionDetail = true/false"

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * Add HTTP exception handler.
     *
     * Set callable to handle scenarios where an error
     * occurs when processing the current request.
     *
     * This service MUST return a callable that accepts
     * three arguments optionally four arguments.
     *
     * 1. Instance of \Psr\Http\Message\ServerRequestInterface
     * 2. Instance of \Psr\Http\Message\ResponseInterface
     * 3. Instance of \Exception
     * 4. Boolean displayErrorDetails (optional)
     *
     * The callable MUST return an instance of
     * \Psr\Http\Message\ResponseInterface.
     *
     * @param string|array          $exceptionTypes
     * @param ErrorHandlerInterface $handler
     */
    // TODO : il faudrait faire un test si on passe un seul attribut qui est un callable dans ce cas c'est qu'on ne précise pas le type d'exception rattaché au handler et donc qu'il s'agit du handler par défaut pour traiter toutes les exceptions. Dans ce cas la méthode setDefaultErrorHandler ne servirai plus à rien !!!
    // TODO : mettre le type du paramétre $handler à RequestHandlerInterface
    // TODO : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php#L85
    // TODO : faire une méthode unbindHandler($name);
    public function bindHandler($exceptionTypes, ErrorHandlerInterface $handler)
    {
        if (! is_array($exceptionTypes)) {
            $exceptionTypes = [$exceptionTypes];
        }

        foreach ($exceptionTypes as $exceptionType) {
            if (! interface_exists($exceptionType) && ! class_exists($exceptionType)) {
                throw new InvalidArgumentException("The class '$exceptionType' doesn't exist, so you can't bind Handler.");
            }
            $this->handlers[$exceptionType] = $handler;
        }
    }

    /**
     * @param string|array $exceptionTypes
     */
    // TODO : réfléchir si on garde cette méthode. Le mieux est de conditionner dans l'application le binding de Throwable avec un booléen dans le fichier de config.
    public function unbindHandler($exceptionTypes)
    {
        if (! is_array($exceptionTypes)) {
            $exceptionTypes = [$exceptionTypes];
        }

        foreach ($exceptionTypes as $exceptionType) {
            // TODO : remplacer par un array_key_exist
            if (isset($this->handlers[$exceptionType])) {
                unset($this->handlers[$exceptionType]);
            }
        }
    }

    /**
     * Get callable to handle scenarios where an error
     * occurs when processing the current request.
     *
     * @param Throwable $exception
     *
     * @return null|ErrorHandlerInterface
     */
    // TODO : réfléchir si on passe cette fonction en public ????
    private function getErrorHandler(Throwable $exception): ?ErrorHandlerInterface
    {
        $errorHandler = null;

        // search from the end of the array because we need to take the last added handler (LIFO style)
        foreach (array_reverse($this->handlers) as $exceptionType => $handler) {
            if (\is_a($exception, $exceptionType)) {
                $errorHandler = $handler;

                break;
            }
        }

        return $errorHandler;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute(self::ORIGINAL_REQUEST, $request);

        set_error_handler($this->createErrorHandler());

        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            $response = $this->handleThrowable($exception, $request);
        }

        restore_error_handler();

        return $response;
    }

    private function handleThrowable(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $request = $request->getAttribute(self::ORIGINAL_REQUEST, false) ?: $request;

        $errorHandler = $this->getErrorHandler($exception);

        // re-throw (bubbleup) the exception if there is no handler found to catch this type of exception
        // this case should only happen if the user have unregistered the default handler for exception instanceof == HttpException
        if (! $errorHandler) {
            throw $exception;
        }

        try {
            $response = $errorHandler->handle($request, $exception, $this->debug);
        } catch (Throwable $e) {
            // TODO : lui passer en paramétre l'exception précédente $exception + l'esception courrante $e et logger en détail ces 2 exceptions.
            $response = $this->handleInternalError($request, $e, $exception);
        }

        return $response;
    }

    /**
     * Handles exception thrown during exception processing in [[handleException()]].
     *
     * @param ServerRequestInterface $request           Request used for the log informations.
     * @param \Throwable             $exception         Exception that was thrown during main exception processing.
     * @param \Throwable             $previousException Main exception processed in [[handleException()]].
     *
     * @return \Psr\Http\Message\ResponseInterface $response The response
     */
    private function handleInternalError(ServerRequestInterface $request, Throwable $exception, Throwable $previousException): ResponseInterface
    {
        // TODO : passer en paramétre du constructeur ce middleware un responseFactory. pour utiliser la méthode Psr $responseFactory->createResponse(500);
        $response = new Response(500);

        $msg = "An Error occurred while handling another error:\n";
        $msg .= (string) $exception;
        $msg .= "\nPrevious exception:\n";
        $msg .= (string) $previousException;

        if ($this->debug) {
            $response->getBody()->write('<pre>' . h($msg) . '</pre>');
        } else {
            $response->getBody()->write('An internal server error occurred.');
        }

        $msg .= "\nRequest Details: " . Serializer::requestToString($request);

        // trace the error in the PHP's system logger.
        error_log($msg);

        return $response;
    }

    /**
     * Creates and returns a callable error handler that raises exceptions.
     *
     * Only raises exceptions for errors that are within the error_reporting mask.
     *
     * @return callable
     */
    // TODO : améliorer avec le code suivant : https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php#L57
    // https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L205
    private function createErrorHandler()
    {
        /*
         * @param int $severity
         * @param string $message
         * @param string $file
         * @param int $line
         * @return void
         * @throws ErrorException if error is not within the error_reporting mask.
         */
        return function (int $severity, string $message, string $file, int $line): void {
            if (error_reporting() & $severity) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        };
    }
}
