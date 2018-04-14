<?php
declare(strict_types = 1);

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

namespace Chiron\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//use Psr\Container\ContainerInterface;

use Chiron\Exception\HttpException;

use Throwable;
use ErrorException;

use function is_a;
use function is_array;
use function is_string;
use function array_filter;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @var string The request attribute name used to store the exception
     */
    // TODO : attention ce nom d'attribut doit être passé comme info à la classe ErrorHandler, car il est aussi présent dans la classe abstraite du ErrorHandler
    private $attributeName = 'Chiron:exception';

    /**
     * Dependency injection container
     *
     * @var ContainerInterface
     */
    //private $container;

    /**
     * @var bool
     */
    private $displayErrorDetails;

    /**
     * Set container
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
    public function __construct(bool $displayErrorDetails)
    {
        $this->displayErrorDetails = $displayErrorDetails;
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
            //throw new \RuntimeException("TEST_Error Processing Request", 1);
            //throw new HttpException(404);
            //throw new \Chiron\Exception\NotFoundHttpException();

            // TODO : je ne pense pas que ce cas peut arriver !!!! à tester mais le dispacher (quand le stackHandler est executé) doit faire cette vérif de mémoire !!!!
            /*
            if (! $response instanceof ResponseInterface) {
                throw new \LogicException('Application did not return a response');
            }*/
        } catch (Throwable $exception) {
            $response = $this->handleThrowable($request, $exception);
//        } catch (Throwable $exception) {
//            $response = $this->handleThrowable($request, new HttpException(500, 'An unexpected error has occurred', $exception));
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
        /**
         * @param int $errno
         * @param string $errstr
         * @param string $errfile
         * @param int $errline
         * @return void
         * @throws ErrorException if error is not within the error_reporting mask.
         */
        return function ($errno, $errstr, $errfile, $errline) {
            if (! (error_reporting() & $errno)) {
                // error_reporting does not include this error
                return;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
    }

    /**
     * Execute the error handler.
     */
    private function handleThrowable(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $exceptionHandler = $this->getExceptionHandler($exception);

        // re-throw the exception if there is no handler found to catch this type of exception
        // this case should only happen if the user have unregistered the default handler for exception instanceof == HttpException
        if (! isset($exceptionHandler)) {
            throw $exception;
        }

        //$params = [$this->request, $this->response, $exception, $this->displayErrorDetails];
        //return call_user_func_array($handler, $params);

// TODO : mettre plutot dans la request un attribute de type tableau : [$exception, $displayDetails, $logError...etc] avec éventuellement directement le array qui est dans la config du container pour la rubrique ErrorHandler
// TODO : au lieu de utiliser une variable "attributeName" il faudrait soit utiliser le nom de la classe : ErrorHandlerMiddleware::class ou alors utiliser une constante de classe genre ErrorHandlerMiddleware::ATTRIBUTE_NAME
        $request = $request->withAttribute($this->attributeName, $exception);
        $request = $request->withAttribute($this->attributeName . '_displayErrorDetails', $this->displayErrorDetails);

        // TODO : il faudra passer le tableau de headers[] qui est dans l'exception aussi dans la response (genre pour une httpexception 405 MethodNotAllowed, on va avoir un headers['Allow' => 'GET', 'POST']) qu'il faut utiliser dans la response.
        /*
        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];
        return JsonResponse::create(
            $this->message,
            $this->statusCode ?: 500,
            $headers
        );*/

        // TODO : on passe en paramétre l'exception plutot que de la stockée dans la request via un attribut, il faudrait éventuellement faire un check si la méthode setException existe on l'utilise. Et ajouter cela dans la doc (pour gérer les erreurs il suffirait que le handler soit une instance de RequestHandlerInterface et qu'il implémente aussi la méthode setException.
        //$exceptionHandler->setException($exception);

// TODO : vérifier si cela à vraiment une utilité !!!!
        // Try to inject the dependency injection container in the error handler
//        if (method_exists($exceptionHandler, 'setContainer') && $this->container instanceof ContainerInterface) {
//            $exceptionHandler->setContainer($this->container);
//        }

        // execute the error handler
        return $exceptionHandler->handle($request);

        // TODO : ajouter le tableau des headers[] présent dans l'exception directement dans la response !!!!
//        $params = [$request, new Response($exception->getStatusCode())];
//        return call_user_func_array($exceptionHandler, $params);
    }



    /**
     * Get callable to handle scenarios where an error
     * occurs when processing the current request.
     *
     * @param HttpException $exception
     * @return null|RequestHandlerInterface
     *
     */
    public function getExceptionHandler(Throwable $exception) : ?RequestHandlerInterface
    {
        $exceptionHandler = null;

        // search from the end of the array because we need to take the last added handler (LIFO style)
        foreach (array_reverse($this->handlers) as $exceptionType => $handler) {
            if (is_a($exception, $exceptionType)) {
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
     * @param string|array     $exceptionTypes
     * @param RequestHandlerInterface $handler
     *
     * @throws \RuntimeException
     */
    // TODO : il faudrait faire un test si on passe un seul attribut qui est un callable dans ce cas c'est qu'on ne précise pas le type d'exception rattaché au handler et donc qu'il s'agit du handler par défaut pour traiter toutes les exceptions. Dans ce cas la méthode setDefaultErrorHandler ne servirai plus à rien !!!
    // TODO : mettre le type du paramétre $handler à RequestHandlerInterface
    // TODO : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php#L85
    public function bindExceptionHandler($exceptionTypes, RequestHandlerInterface $handler)
    {
        if (! is_array($exceptionTypes)) {
            $exceptionTypes = [$exceptionTypes];
        }

        foreach ($exceptionTypes as $exceptionType) {
            $this->handlers[$exceptionType] = $handler;
        }
    }
}
