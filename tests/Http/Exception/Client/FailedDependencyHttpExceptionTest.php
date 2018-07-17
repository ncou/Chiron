<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\FailedDependencyHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class FailedDependencyHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new FailedDependencyHttpException();
    }
}
