<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use ErrorException;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

//https://github.com/spiral/exceptions/blob/master/src/AbstractHandler.php

// TODO : améliorer la fonction de log en utilisant ce bout de code => https://github.com/cakephp/cakephp/blob/master/src/Error/Middleware/ErrorHandlerMiddleware.php#L211
// autre exemple ici : https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L334
class LoggerReporter implements ReporterInterface
{
    /**
     * Current minimum logging threshold.
     *
     * @var string
     */
    private $logLevelThreshold;

    /**
     * PHP to PSR3 error levels map.
     *
     * @var array
     */
    // TODO : permettre de customiser via une méthode cette map ????
    //https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L396
    //https://github.com/Seldaek/monolog/blob/master/src/Monolog/ErrorHandler.php#L127
    //https://github.com/zendframework/zend-log/blob/master/src/Logger.php#L45
    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Debug/ErrorHandler.php#L71
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
     * Logging level mapping, used to hierarchize
     * @const int defined from the BSD Syslog message severities
     *
     * This complies to \Psr\Log\LogLevel and RFC 5424 severity values :
     *   0  Emergency: system is unusable
     *   1  Alert: action must be taken immediately
     *   2  Critical: critical conditions
     *   3  Error: error conditions
     *   4  Warning: warning conditions
     *   5  Notice: normal but significant condition
     *   6  Informational: informational messages
     *   7  Debug: debug-level messages
     *
     * @link https://tools.ietf.org/html/rfc5424#page-11
     * @link https://tools.ietf.org/html/rfc3164#page-9
     *
     * @var array
     */
    // TODO : https://github.com/zendframework/zend-log/blob/master/src/Logger.php#L29
    // TODO : https://github.com/ozh/log/blob/master/src/Logger.php#L59
    private $logLevels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
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

    public function setLevelMap(array $levelMap)
    {
        $this->levelMap = array_replace($this->levelMap, $levelMap);
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
        $message = $this->getMessage($request, $e);

        $this->logger->log($level, $message);
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
        // TODO : mettre ce bout de code dans une méthode "requestContext($request)" qui retournerai une string https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L334
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
    // TODO : améliorer avec le bout de code : https://github.com/cakephp/cakephp/blob/2341c3cd7c32e315c2d54b625313ef55a86ca9cc/src/Error/BaseErrorHandler.php#L356
    // TODO : améliorer la méthode getClass() pour gérer les classes anonymes : https://github.com/Seldaek/monolog/blob/master/src/Monolog/Utils.php#L19
    private function getMessageForError(Throwable $e, bool $isPrevious = false): string
    {

/*
        $this->logger->log(
            $level,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', Utils::getClass($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            ['exception' => $e]
        );
*/

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

        // TODO : utiliser la méthode shouldLog():bool
        return $this->logLevels[$level] <= $this->logLevels[$this->logLevelThreshold];
    }

    /**
     * @return bool
     */
    // TODO : renommer en canLog
    /*
    private function shouldLog($level): bool
    {
        return $this->logLevels[$level] <= $this->logLevels[$this->logLevelThreshold];
    }*/
}
