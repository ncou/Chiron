<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\UnprocessableEntityHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class UnprocessableEntityHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new UnprocessableEntityHttpException();
    }
}
