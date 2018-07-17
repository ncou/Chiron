<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\ExpectationFailedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class ExpectationFailedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new ExpectationFailedHttpException();
    }
}
