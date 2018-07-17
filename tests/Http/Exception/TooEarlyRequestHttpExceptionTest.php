<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\TooEarlyRequestHttpException;

class TooEarlyRequestHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new TooEarlyRequestHttpException();
    }
}
