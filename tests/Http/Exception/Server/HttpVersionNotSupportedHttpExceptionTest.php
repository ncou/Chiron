<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\HttpVersionNotSupportedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class HttpVersionNotSupportedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new HttpVersionNotSupportedHttpException();
    }
}
