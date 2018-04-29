<?php

declare(strict_types=1);

namespace Chiron;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * Minimalist PSR-3 logger designed to write in stderr or any other stream.
 */
class Logger extends AbstractLogger
{
    public const LEVELS = [
        LogLevel::DEBUG         => 0,
        LogLevel::INFO          => 1,
        LogLevel::NOTICE        => 2,
        LogLevel::WARNING       => 3,
        LogLevel::ERROR         => 4,
        LogLevel::CRITICAL      => 5,
        LogLevel::ALERT         => 6,
        LogLevel::EMERGENCY     => 7,
    ];

    private $minLevelIndex;

    private $formatter;

    private $handle;

    public function __construct($output = 'php://stderr', string $minLevel = LogLevel::ERROR, callable $formatter = null)
    {
        // TODO : creer une methode assertLevel() qui fait le throw de l'exception si le ne level n'est pas correct.
        if (! isset(self::LEVELS[$minLevel])) {
            throw new InvalidArgumentException('Invalid log level. Must be one of : ' . implode(', ', array_keys(self::LEVELS)));
        }
        if (false === $this->handle = is_resource($output) ? $output : @fopen($output, 'a')) {
            throw new InvalidArgumentException(sprintf('Unable to open "%s".', $output));
        }

        // TODO : créer une méthode setMinLevel() pour permettre de modifier le niveau minimal de verbosité apres avoir instancié le logger.
        $this->minLevelIndex = self::LEVELS[$minLevel];
        $this->formatter = $formatter ?: [$this, 'format'];
    }

    /**
     * Log the message (string or Object with __string function) in the opened log file.
     *
     * @param string $level
     * @param mixed  $message Should be a string a scalar or an object with __string() function.
     * @param array  $context
     *
     * @return string
     */
    public function log($level, $message, array $context = [])
    {
        // TODO : creer une methode assertLevel() qui fait le throw de l'exception si le ne level n'est pas correct.
        if (! isset(self::LEVELS[$level])) {
            throw new InvalidArgumentException('Invalid log level. Must be one of : ' . implode(', ', array_keys(self::LEVELS)));
        }
        if (self::LEVELS[$level] < $this->minLevelIndex) {
            return;
        }
        // TODO : throw an exception if the $message IS NOT a string OR a scalar OR an object with __string() ?
        $result = call_user_func_array($this->formatter, [$level, (string) $message, $context]);
        fwrite($this->handle, $result);
    }

    /**
     * Default formatter if none is specified in the constructor.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    private function format(string $level, string $message, array $context): string
    {
        $message = $this->interpolate($message, $context);

        return sprintf('%s [%s] %s', date(\DateTime::RFC3339), strtoupper($level), $message);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    private function interpolate(string $message, array $context = [])
    {
        if (strpos($message, '{') !== false) {
            $replacements = [];
            foreach ($context as $key => $val) {
                if (null === $val || is_scalar($val) || (\is_object($val) && method_exists($val, '__toString'))) {
                    $replacements["{{$key}}"] = $val;
                } elseif ($val instanceof \DateTimeInterface) {
                    $replacements["{{$key}}"] = $val->format(\DateTime::RFC3339);
                } elseif (\is_object($val)) {
                    $replacements["{{$key}}"] = '[object ' . \get_class($val) . ']';
                } else {
                    $replacements["{{$key}}"] = '[' . \gettype($val) . ']';
                }
            }
            $message = strtr($message, $replacements);
        }

        return $message;
    }
}
