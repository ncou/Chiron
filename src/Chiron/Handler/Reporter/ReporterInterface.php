<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Interface for classes that implement a reporter for the exceptions.
 */
interface ReporterInterface
{
    /**
     * Report the exception.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                              $e
     */
    public function report(ServerRequestInterface $request, Throwable $e): void;

    /**
     * Can we report the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canReport(Throwable $e): bool;
}
