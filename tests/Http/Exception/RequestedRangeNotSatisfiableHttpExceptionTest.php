<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestedRangeNotSatisfiableHttpException;

class RequestedRangeNotSatisfiableHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestedRangeNotSatisfiableHttpException();
    }
}
