<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestEntityTooLargeHttpException;

class RequestEntityTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestEntityTooLargeHttpException();
    }
}
