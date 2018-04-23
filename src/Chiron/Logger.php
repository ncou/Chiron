<?php

declare(strict_types=1);

namespace Chiron;

use InvalidArgumentException;
use Psr\Log\AbstractLogger;
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

    private $handle;

    public function __construct($output = 'php://stderr', string $minLevel = LogLevel::ERROR)
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
    }

    public function log($level, $message, array $context = [])
    {
        // TODO : creer une methode assertLevel() qui fait le throw de l'exception si le ne level n'est pas correct.
        if (! isset(self::LEVELS[$level])) {
            throw new InvalidArgumentException('Invalid log level. Must be one of : ' . implode(', ', array_keys(self::LEVELS)));
        }
        if (self::LEVELS[$level] < $this->minLevelIndex) {
            return;
        }
        fwrite($this->handle, $this->format($level, $message, $context));
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    private function format(string $level, string $message, array $context): string
    {
        if (false !== strpos($message, '{')) {
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

        return sprintf('%s [%s] %s', date(\DateTime::RFC3339), strtoupper($level), $message);
    }
}
