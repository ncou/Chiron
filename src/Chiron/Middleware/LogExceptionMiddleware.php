<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

// TODO ; ajouter une description du middleware + ajouter de la phpdoc en entête des fonctions de la classe

// TODO : regarder ici quelques exemples : https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php    notamment : https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php#L125

final class LogExceptionMiddleware implements MiddlewareInterface
{
    private $logger;

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

    // TODO : passer en paramétre le choix de l'utilisateur pour la map des levels, cad un tableau comme celui là $this->errorLevelMap et on fera un array_replace() sur ce tableau avec celui passé en paramétre par l'utilisateur, par défaut on mettra [].   Faire de même pour le callable qui formatera l'exception.
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // TODO : à virer c'est un test !!!!!!!!!!!
            //trigger_error("This event WILL fire", E_USER_ERROR);
            //throw new ErrorHandler($errstr, 0, $errno, $errfile, $errline);

            return $handler->handle($request);
        } catch (ErrorException $e) {
            $level = $this->errorLevelMap[$e->getSeverity()];
            $this->logger->log($level, $this->formatException($e));

            throw $e;
        } catch (Throwable $e) {
            $this->logger->critical($this->formatException($e));

            throw $e;
        }
    }

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
}
