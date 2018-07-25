<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;


use Jgut\HttpException\ForbiddenHttpException;
use Jgut\HttpException\HttpException;
use Jgut\HttpException\InternalServerErrorHttpException;
use Jgut\HttpException\MethodNotAllowedHttpException;
use Jgut\HttpException\NotFoundHttpException;
use Jgut\HttpException\UnauthorizedHttpException;
use Jgut\Slim\Exception\Whoops\Formatter\Text;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Chiron\Http\Psr\Response;
use InvalidArgumentException;
use ErrorException;
use Throwable;

//https://github.com/cakephp/cakephp/blob/master/src/Error/BaseErrorHandler.php#L390
//https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php#L125

/**
 * HTTP Exceptions Manager.
 *
 */
class ExceptionManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * PHP to PSR3 error map.
     *
     * @var array
     */
    private $errorLevelMap = [
        E_ERROR             => LogLevel::CRITICAL,
        E_WARNING           => LogLevel::WARNING,
        E_PARSE             => LogLevel::ALERT,
        E_NOTICE            => LogLevel::NOTICE,
        E_CORE_ERROR        => LogLevel::CRITICAL,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_COMPILE_ERROR     => LogLevel::ALERT,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_USER_ERROR        => LogLevel::ERROR,
        E_USER_WARNING      => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::NOTICE,
        E_STRICT            => LogLevel::NOTICE,
        E_RECOVERABLE_ERROR => LogLevel::ERROR,
        E_DEPRECATED        => LogLevel::NOTICE,
        E_USER_DEPRECATED   => LogLevel::NOTICE,
    ];

    /**
     * List of HTTP exception handlers.
     *
     * @var ExceptionHandlerInterface[]
     */
    private $handlers = [];

    /**
     * Default HTTP status code handler.
     *
     * @var ExceptionHandler
     */
    private $defaultHandler;

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
     * @param HttpException $exception
     *
     * @return null|ExceptionHandlerInterface
     */

    public function getExceptionHandler(Throwable $exception): ?ExceptionHandlerInterface
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
     * @param string|array            $exceptionTypes
     * @param ExceptionHandlerInterface $handler
     */
    // TODO : il faudrait faire un test si on passe un seul attribut qui est un callable dans ce cas c'est qu'on ne précise pas le type d'exception rattaché au handler et donc qu'il s'agit du handler par défaut pour traiter toutes les exceptions. Dans ce cas la méthode setDefaultErrorHandler ne servirai plus à rien !!!
    // TODO : mettre le type du paramétre $handler à RequestHandlerInterface
    // TODO : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Error/ExceptionHandlerManager.php#L85
    public function bindExceptionHandler($exceptionTypes, ExceptionHandlerInterface $handler)
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
     * Set default HTTP status code handler.
     *
     * @param ExceptionHandler $defaultHandler
     */
    /*
    public function setDefaultHandler(ExceptionHandler $defaultHandler)
    {
        $this->defaultHandler = $defaultHandler;
    }*/

    /**
     * Add HTTP exception handler.
     *
     * @param string|array     $exceptionTypes
     * @param ExceptionHandler $handler
     */
    /*
    public function addHandler($exceptionTypes, ExceptionHandler $handler)
    {
        if (!\is_array($exceptionTypes)) {
            $exceptionTypes = [$exceptionTypes];
        }

        $exceptionTypes = \array_filter(
            $exceptionTypes,
            function ($exceptionType): bool {
                return \is_string($exceptionType);
            }
        );

        foreach ($exceptionTypes as $exceptionType) {
            $this->handlers[$exceptionType] = $handler;
        }
    }*/

    /**
     * Helper - Add Unauthorized exception handler.
     *
     * @param ExceptionHandlerInterface $handler
     */
    public function addUnauthorizedHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[ExceptionHelper::getExceptionNameByStatusCode(401)] = $handler;
    }

    /**
     * Helper - Add Forbidden exception handler.
     *
     * @param ExceptionHandlerInterface $handler
     */
    public function addForbiddenHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[ExceptionHelper::getExceptionNameByStatusCode(403)] = $handler;
    }

    /**
     * Helper - Add Not Found exception handler.
     *
     * @param ExceptionHandlerInterface $handler
     */
    public function addNotFoundHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[ExceptionHelper::getExceptionNameByStatusCode(404)] = $handler;
    }

    /**
     * Helper - Add Method Not Allowed exception handler.
     *
     * @param ExceptionHandlerInterface $handler
     */
    public function addMethodNotAllowedHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[ExceptionHelper::getExceptionNameByStatusCode(405)] = $handler;
    }

    /**
     * Helper - Add ServiceUnavailable exception handler.
     *
     * @param ExceptionHandlerInterface $handler
     */
    public function addServiceUnavailableHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[ExceptionHelper::getExceptionNameByStatusCode(503)] = $handler;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param HttpException          $exception
     *
     * @return ResponseInterface
     */
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $exceptionHandler = $this->getExceptionHandler($exception);

        // re-throw the exception if there is no handler found to catch this type of exception
        // this case should only happen if the user have unregistered the default handler for exception instanceof == HttpException
        if (empty($exceptionHandler)) {
            throw $exception;
        }

        // Log the exception and some request informations
        $this->log($exception, $request);

        return $exceptionHandler->handleException($exception, $request);
    }

    /**
     * Log exception.
     *
     * @param Throwablable          $e
     * @param ServerRequestInterface $request
     */
    private function log(Throwable $e, ServerRequestInterface $request)
    {
        if (! $this->logger) {
            return;
        }

        $level = $this->getLogLevel($e);

// TODO : regarder pour analyser le context, voir si on pourra l'utiliser !!!!
/*
        $logContext = [
            'http_method' => $request->getMethod(),
            'request_uri' => (string) $request->getUri(),
            'level_name' => \strtoupper($level),
            'stack_trace' => $this->getStackTrace($e),
        ];

        $this->logger->log($level, $exception->getMessage(), $logContext);
        */


        $this->logger->log($level, $this->formatException($e));
        //$this->logger->log($level, $exception->getMessage().$exception->getTraceAsString());
        // $output = Formatter::formatExceptionPlain(new Inspector($exception));

    }

    /**
     * Get exception stack trace.
     *
     * @param HttpException $exception
     *
     * @return string
     */
    /*
    private function getStackTrace(Throwable $exception): string
    {
        if (!\class_exists('Whoops\Run')) {
            // @codeCoverageIgnoreStart
            return $exception->getTraceAsString();
            // @codeCoverageIgnoreEnd
        }

        $formatter = new Text();
        $formatter->setException($exception);
        $exceptionParts = \explode("\n", \rtrim($formatter->generateResponse(), "\n"));

        if (\count($exceptionParts) !== 1) {
            return \implode("\n", \array_filter(\array_splice($exceptionParts, 2)));
        }

        return '';
    }*/

    /**
     * Create plain text response and return it as a string.
     *
     * @param Throwable $e
     *
     * @return string
     */
    private function formatException(Throwable $e): string
    {
        return sprintf(
            "%s: %s in file %s on line %d\r\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $this->formatExceptionTraces($e->getTrace())
        );
    }

    /**
     * @param array $traces
     *
     * @return string
     */
    // TODO : améliorer le formatage : https://github.com/filp/whoops/blob/master/src/Whoops/Exception/Formatter.php#L59
    private function formatExceptionTraces(array $traces): string
    {
        $trace = '';
        foreach ($traces as $index => $record) {
            $trace .= sprintf(
                "    #%s %s%s%s() called at %s:%s\r\n",
                $index,
                $record['class'] ?? '',
                isset($record['class'], $record['function']) ? $record['type'] : '',
                $record['function'] ?? '',
                $record['file'] ?? 'unknown',
                $record['line'] ?? 0
            );
        }

        return $trace;
    }

    /**
     * Get log level to use for the PSR3 Logger.
     * By default for the NON 'ErrorException' exception it will always be 'CRITICAL'
     *
     * @param Throwable $e
     *
     * @return string
     */
    final public function getLogLevel(Throwable $e): string
    {
        if ($e instanceof ErrorException && \array_key_exists($e->getSeverity(), $this->errorLevelMap)) {
            return $this->errorLevelMap[$e->getSeverity()];
        }

        // default log level for Throwable
        return LogLevel::CRITICAL;
    }
}
