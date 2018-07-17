<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\RequestUriTooLongHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class RequestUriTooLongHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestUriTooLongHttpException();
    }
}
