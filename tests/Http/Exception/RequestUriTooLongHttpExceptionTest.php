<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\RequestUriTooLongHttpException;

class RequestUriTooLongHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new RequestUriTooLongHttpException();
    }
}
