<?php

declare(strict_types=1);

namespace Chiron\Exception\Reporter;

use Rollbar;
use Throwable;

class RollbarReporter implements ReporterInterface
{
    /**
     * RollbarReporter constructor.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config)
    {
        Rollbar::init($config);
    }

    /**
     * Report exception.
     *
     * @param Throwable $e
     *
     * @return string|void
     */
    public function report(Throwable $e): void
    {
        Rollbar::report_exception($e);
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
        // check if Rollbar client is installed.
        return class_exists(Rollbar::class);
    }
}
