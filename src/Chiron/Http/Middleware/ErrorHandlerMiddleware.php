<?php

declare(strict_types=1);

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

namespace Chiron\Http\Middleware;

use Chiron\Handler\Error\ExceptionManager;
use Chiron\Http\Exception\HttpException;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
//use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
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
        try {
            set_error_handler($this->createErrorHandler());
            $response = $handler->handle($request);

            // TODO : à virer c'est pour tester !!!!
            //throw new \RuntimeException("TEST_Error 'Processing' \"Request\"", 1);
            //throw new HttpException(404);
            //throw new \Chiron\Exception\NotFoundHttpException();

            // TODO : je ne pense pas que ce cas peut arriver !!!! à tester mais le dispacher (quand le stackHandler est executé) doit faire cette vérif de mémoire !!!!
            /*
            if (! $response instanceof ResponseInterface) {
                throw new \LogicException('Application did not return a response');
            }*/
        } catch (Throwable $exception) {
            //$response = $this->handleThrowable($exception, $request);
            $response = $this->exceptionManager->handleException($exception, $request);
        } finally {
            restore_error_handler();
        }

        return $response;
    }

    /**
     * Creates and returns a callable error handler that raises exceptions.
     *
     * Only raises exceptions for errors that are within the error_reporting mask.
     *
     * @return callable
     */
    private function createErrorHandler()
    {
        /*
         * @param int $errno
         * @param string $errstr
         * @param string $errfile
         * @param int $errline
         * @return void
         * @throws ErrorException if error is not within the error_reporting mask.
         */
        return function (int $errno, string $errstr, string $errfile, int $errline): void {
            //return function ($errno, $errstr, $errfile, $errline) {
            if (! (error_reporting() & $errno)) {
                // error_reporting does not include this error
                return;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
    }
}
