<?php

declare(strict_types=1);

namespace Chiron\Exception;

use Chiron\Http\Psr\Response;
use InvalidArgumentException;
use Jgut\HttpException\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Exception\HandlerInterface;
use Throwable;

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

/**
 * HTTP Exceptions Manager.
 */
class ExceptionManager
{
    /**
     * List of HTTP exception handlers.
     *
     * @var HandlerInterface[]
     */
    private $handlers = [];

    /**
     * Default HTTP status code handler.
     *
     * @var ExceptionHandler
     */
    //private $defaultHandler;

    /**
     * HttpExceptionManager constructor.
     *
     * @param ExceptionHandler $defaultHandler
     */
    /*
    public function __construct(ExceptionHandler $defaultHandler)
    {
        $this->setDefaultHandler($defaultHandler);
    }*/

    /**
     * Get callable to handle scenarios where an error
     * occurs when processing the current request.
     *
     * @param Throwable $exception
     *
     * @return null|HandlerInterface
     */
    // TODO : passer cette méthode en private !!!!
    public function getExceptionHandler(Throwable $exception): ?HandlerInterface
    {
        $exceptionHandler = null;

        // search from the end of the array because we need to take the last added handler (LIFO style)
        foreach (array_reverse($this->handlers) as $exceptionType => $handler) {
            if (\is_a($exception, $exceptionType)) {
                $exceptionHandler = $handler;

                break;
            }
        }

        return $exceptionHandler;
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
     * @param string|array              $exceptionTypes
     * @param HandlerInterface $handler
     */
    // TODO : il faudrait faire un test si on passe un seul attribut qui est un callable dans ce cas c'est qu'on ne précise pas le type d'exception rattaché au handler et donc qu'il s'agit du handler par défaut pour traiter toutes les exceptions. Dans ce cas la méthode setDefaultErrorHandler ne servirai plus à rien !!!
    // TODO : mettre le type du paramétre $handler à RequestHandlerInterface
    // TODO : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php#L85
    public function bindExceptionHandler($exceptionTypes, HandlerInterface $handler)
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
     * @param Throwable              $exception
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    // TODO : renommer en generateResponse() ????
    // TODO : essayer de faire une réponse par défaut si il y a une exception dans une classe Reporter ou Formater ???? : https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L121
    public function renderException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $exceptionHandler = $this->getExceptionHandler($exception);

        // re-throw the exception if there is no handler found to catch this type of exception
        // this case should only happen if the user have unregistered the default handler for exception instanceof == HttpExceptionInterface
        if (empty($exceptionHandler)) {
            throw $exception;
        }

        // Log the exception and some request informations
        $exceptionHandler->report($exception, $request);
        // generate the error response to use.
        return $exceptionHandler->render($exception, $request);
    }
}
