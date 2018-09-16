<?php

declare(strict_types=1);

namespace Chiron\Handler\Error\Reporter;

use Chiron\Http\Psr\Response;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class LoggerReporter implements ExceptionReporterInterface
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
    // TODO : créer une classe "LogReporter" et ReporterInterface pour externaliser le code et permettre de mettre plusieurs reporters
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
