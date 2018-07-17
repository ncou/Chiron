<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\RequestedRangeNotSatisfiableHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class RequestedRangeNotSatisfiableHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestedRangeNotSatisfiableHttpException();
    }
}
