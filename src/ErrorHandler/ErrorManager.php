<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Chiron\Http\Exception\HttpException;
//use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Response;
use Chiron\Support\Http\Serializer;
use ErrorException;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Throwable;

// TODO : classe à renommer en ExceptionManager ?????
class ErrorManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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

    // TODO : lui passer un ResponseFactoryInterface en paramétre et un LoggerInterface aussi en paramétre
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

    // TODO : méthode à renommer en render() ????
    // TODO : passer en paramétre de la fonction la valeur de debug ??? cad ajouter un 3eme paramétre à cette fonction ????
    public function renderException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $errorHandler = $this->getErrorHandler($exception);

        // re-throw (bubbleup) the exception if there is no handler found to catch this type of exception
        // this case should only happen if the user have unregistered the default handler for exception instanceof == HttpException
        if (! $errorHandler) {
            throw $exception;
        }

        try {
            $this->logError($exception);
            $response = $errorHandler->renderException($exception, $request, $this->debug);
        } catch (Throwable $e) {
            // TODO : lui passer en paramétre l'exception précédente $exception + l'esception courrante $e et logger en détail ces 2 exceptions.
            // TODO : on devrait peut etre déplacer ce bout de code pour gérer les erreurs internes directement dans la classe ErrorHandler (qui posséde déjà un responseFactory !!!)
            $response = $this->renderInternalException($request, $e, $exception);
        }

        return $response;
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
     * Execute all the reporters in the stack.
     *
     * @param \Throwable $e
     */
    // TODO : utiliser la méthode Debug::formatException pour formatter l'exception à logger !!!!
    private function logError(Throwable $e): void
    {
        if ($this->logger !== null) {
            //$class = $e instanceof ErrorException ? Debug::translateErrorCode($e->getSeverity()) : Debug::getClass($e);
            $class = get_class($e);

            // replace invisible ascii characters (range 0-9 and 11-31 except the new line character 10) with a single space character.
            $message = preg_replace('#[\x00-\x09\x0B-\x1F]+#', ' ', $e->getMessage());

            // exceptions are string-convertible, thus should be passed as it is to the logger
            $context['exception'] = $e;
            $context['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $context['time'] = microtime(true);
            $context['memory'] = memory_get_usage();

            $this->logger->error(sprintf('Uncaught Exception %s: "%s" at %s line %s', $class, $message, $e->getFile(), $e->getLine()), $context);
            //$this->logger->log(LogLevel::ERROR, sprintf('Uncaught Exception %s: "%s" at %s line %s', $class, $message, $e->getFile(), $e->getLine()), $context);
        }
    }

    /**
     * Handles exception thrown during exception processing in [[handleException()]].
     *
     * @param ServerRequestInterface $request           Request used for the log informations.
     * @param \Throwable             $exception         Exception that was thrown during main exception processing.
     * @param \Throwable             $originalException Main exception processed in [[handleException()]].
     *
     * @return \Psr\Http\Message\ResponseInterface $response The response
     */
    // TODO : utiliser la méthode Debug::formatException pour formatter l'exception à logger !!!!
    // TODO : utiliser une méthode htmlentities pour encoder les caractéres spéciaux ????
    private function renderInternalException(ServerRequestInterface $request, Throwable $exception, Throwable $originalException): ResponseInterface
    {
        // TODO : passer en paramétre du constructeur ce middleware un responseFactory. pour utiliser la méthode Psr $responseFactory->createResponse(500);
        //$response = new Response(500);
        $response = \Chiron\ResponseCreator\Facade\ResponseCreator::create(500); // TODO : attention ce code est risqué car il peut il y avoir une erreur !!!!

        $msg = "An Error occurred while handling another error:\n";
        $msg .= (string) $exception;
        $msg .= "\nOriginal exception:\n";
        $msg .= (string) $originalException;

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
}
