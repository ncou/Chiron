<?php

declare(strict_types=1);

// originalRequest : https://github.com/zendframework/zend-expressive/blob/c6db5b1a7524414eee0637bb50b8eed32fd67794/src/Middleware/WhoopsErrorResponseGenerator.php

// Gérer le cas ou il y a une erreur en interne dans le handler :
//https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php#L138
//https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/ErrorHandler.php#L141

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

namespace Chiron\Http\Middleware;

use Chiron\Handler\ExceptionManager;
use Chiron\Http\Psr\Response;
use ErrorException;
use Exception;
//use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    public const ORIGINAL_REQUEST = '__originalRequest__';

    /**
     * @var ExceptionManager
     */
    private $exceptionManager;

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
    public function __construct(ExceptionManager $exceptionManager)
    {
        $this->exceptionManager = $exceptionManager;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /*
                    error_reporting(E_ALL);
                    if (! $app->environment('testing')) {
                        ini_set('display_errors', 'Off'); // '0'
                    }
        */

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

        try {
            $response = $this->exceptionManager->renderException($exception, $request);
        } catch (Throwable $exception) {
            $response = $this->handleInternalError();
        }

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     */
    private function handleInternalError(): ResponseInterface
    {
        // TODO : passer en paramétre de ce middleware un responseFactory. pour utiliser la méthode Psr $responseFactory->create(500);
        $response = new Response();
        $response->getBody()->write('An Internal Server Error Occurred');

        return $response->withStatus(500);
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

