<?php

declare(strict_types=1);

namespace Chiron\Dispatcher;

use Chiron\Console\Console;
use Chiron\Console\ExitCode;
use Throwable;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
final class ConsoleDispatcher extends AbstractDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return php_sapi_name() === 'cli' && env('RR') === null;
    }

    /**
     * @param Console   $console
     *
     * @return int
     */
    protected function perform(Console $console): int
    {
        try {
            return $console->run();
        } catch (Throwable $e) {
            $console->handleException($e);

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
