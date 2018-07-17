<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class RequestHeaderFieldsTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestHeaderFieldsTooLargeHttpException();
    }
}
