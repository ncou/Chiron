<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\TooEarlyRequestHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class TooEarlyRequestHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new TooEarlyRequestHttpException();
    }
}
