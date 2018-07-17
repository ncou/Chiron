<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\RequestTimeoutHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class RequestTimeoutHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestTimeoutHttpException();
    }
}
