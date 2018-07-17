<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\LengthRequiredHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class LengthRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new LengthRequiredHttpException();
    }
}
