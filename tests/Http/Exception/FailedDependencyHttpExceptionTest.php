<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\FailedDependencyHttpException;

class FailedDependencyHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new FailedDependencyHttpException();
    }
}