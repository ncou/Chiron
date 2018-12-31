<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use Chiron\Http\Psr\Response;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

// TODO : améliorer la fonction de log en utilisant ce bout de code => https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php#L211
// autre exemple ici : https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L334
// TODO : passer en paramétre de la méthode report la Request et logguer le $request->getRequestTarget() + $request->getHeaderLine('Referer') et voir même pour logger l'IP
class LoggerReporter implements ReporterInterface
{
    /**
     * PHP to PSR3 error levels map.
     *
     * @var array
     */
    private $levelMap = [
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
     * Create a new exception handler instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable                               $e
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function report(Throwable $e): void
    {
        $level = $this->getLogLevel($e);
        $this->logger->log($level, $this->formatException($e));
    }

    /**
     * Get log level to use for the PSR3 Logger.
     * By default for the NON 'ErrorException' exception it will always be 'CRITICAL'.
     *
     * @param Throwable $e
     *
     * @return string
     */
    public function getLogLevel(Throwable $e): string
    {
        if ($e instanceof ErrorException && \array_key_exists($e->getSeverity(), $this->levelMap)) {
            return $this->levelMap[$e->getSeverity()];
        }

        // default log level for Throwable
        return LogLevel::CRITICAL;
    }

    /**
     * Create plain text response and return it as a string.
     *
     * @param Throwable $e
     *
     * @return string
     */
    // TODO : améliorer le code, on fait quoi si il y a aussi une exception dans Previous porté par la Throwable ???? elle ne sera pas logguée !!!!
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
    private function formatExceptionTraces(array $frames): string
    {
        $trace = '';
        foreach ($frames as $i => $frame) {
            $trace .= sprintf(
                "    #%u %s%s%s() called at %s:%u\r\n",
                count($frames) - $i - 1,
                $frame['class'] ?? '',
                isset($frame['class'], $frame['function']) ? $frame['type'] : '',
                $frame['function'] ?? '',
                $frame['file'] ?? '<#unknown>',
                $frame['line'] ?? 0
            );
        }

        return $trace;
    }

    /**
     * Can we report the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canReport(Throwable $e): bool
    {
        return true;
    }
}
