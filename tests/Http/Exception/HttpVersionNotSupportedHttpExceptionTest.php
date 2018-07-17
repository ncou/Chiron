<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\HttpVersionNotSupportedHttpException;

class HttpVersionNotSupportedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new HttpVersionNotSupportedHttpException();
    }
}
