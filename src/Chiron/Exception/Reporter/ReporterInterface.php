<?php

declare(strict_types=1);

namespace Chiron\Exception\Reporter;

use Throwable;

/**
 * Interface for classes that implement a reporter for the exceptions.
 */
interface ReporterInterface
{
    /**
     * Report the exception.
     *
     * @param \Throwable $e
     */
    public function report(Throwable $e): void;

    /**
     * Can we report the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canReport(Throwable $e): bool;
}
