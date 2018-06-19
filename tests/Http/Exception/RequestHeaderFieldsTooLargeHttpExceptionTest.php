<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestHeaderFieldsTooLargeHttpException;

class RequestHeaderFieldsTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestHeaderFieldsTooLargeHttpException();
    }
}
