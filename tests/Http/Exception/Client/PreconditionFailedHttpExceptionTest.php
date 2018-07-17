<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Client\PreconditionFailedHttpException;

class PreconditionFailedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PreconditionFailedHttpException();
    }
}
