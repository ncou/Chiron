<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function call_user_func_array;

class CallableReporter implements ReporterInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * Create a new exception handler instance.
     *
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Report or log an exception.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $e
     */
    public function report(ServerRequestInterface $request, Throwable $e): void
    {
        call_user_func_array($this->callable, [$request, $e]);
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
