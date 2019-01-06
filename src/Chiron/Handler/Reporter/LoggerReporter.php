<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use ErrorException;

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
    // TODO : permettre de customiser via une méthode cette map ????
    //https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L396
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
     * Current minimum logging threshold.
     *
     * @var string
     */
    private $logLevelThreshold;

    /**
     * Log Levels.
     *
     * @var array
     */
    private $logLevels = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT     => 6,
        LogLevel::CRITICAL  => 5,
        LogLevel::ERROR     => 4,
        LogLevel::WARNING   => 3,
        LogLevel::NOTICE    => 2,
        LogLevel::INFO      => 1,
        LogLevel::DEBUG     => 0,
    ];

    /**
     * Create a new exception handler instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, string $logLevelThreshold = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->setLogLevelThreshold($logLevelThreshold);
    }

    /**
     * Sets the Log Level Threshold.
     *
     * @param string $logLevelThreshold The log level threshold
     */
    public function setLogLevelThreshold(string $logLevelThreshold): void
    {
        if (! array_key_exists($logLevelThreshold, $this->logLevels)) {
            throw new InvalidArgumentException('Invalid log level. Must be one of : ' . implode(', ', array_keys($this->logLevels)));
        }

        $this->logLevelThreshold = $logLevelThreshold;
    }

    /**
     * Report or log an exception.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $e
     */
    public function report(ServerRequestInterface $request, Throwable $e): void
    {
        $level = $this->getLogLevel($e);
        $this->logger->log($level, $this->getMessage($request, $e));
    }

    /**
     * Get log level to use for the PSR3 Logger.
     * By default for the NON 'ErrorException' exception it will always be 'CRITICAL'.
     *
     * @param Throwable $e
     *
     * @return string
     */
    private function getLogLevel(Throwable $e): string
    {
        if ($e instanceof ErrorException && array_key_exists($e->getSeverity(), $this->levelMap)) {
            return $this->levelMap[$e->getSeverity()];
        }

        // default log level for Throwable
        return LogLevel::CRITICAL;
    }

    /**
     * Generate the error log message.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The current request.
     * @param \Throwable                               $e       The exception to log a message for.
     *
     * @return string Error message
     */
    private function getMessage(ServerRequestInterface $request, Throwable $e): string
    {
        $message = $this->getMessageForError($e);
        $message .= "\nRequest URL: " . $request->getRequestTarget();
        $referer = $request->getHeaderLine('Referer');
        if ($referer) {
            $message .= "\nReferer URL: " . $referer;
        }
        $message .= "\n\n";

        return $message;
    }

    /**
     * Generate the message for the error.
     *
     * @param \Throwable $e          The exception to log a message for.
     * @param bool       $isPrevious False for original exception, true for previous
     *
     * @return string Error message
     */
    private function getMessageForError(Throwable $e, bool $isPrevious = false): string
    {
        $message = sprintf(
            '%s[%s] %s',
            $isPrevious ? "\nCaused by: " : '',
            get_class($e),
            $e->getMessage()
        );

        $message .= "\n" . $e->getTraceAsString();

        $previous = $e->getPrevious();
        if ($previous) {
            $message .= $this->getMessageForError($previous, true);
        }

        return $message;
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
        $level = $this->getLogLevel($e);

        return $this->logLevels[$level] >= $this->logLevels[$this->logLevelThreshold];
    }
}
