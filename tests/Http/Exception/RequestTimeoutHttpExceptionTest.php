<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestTimeoutHttpException;

class RequestTimeoutHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestTimeoutHttpException();
    }
}
