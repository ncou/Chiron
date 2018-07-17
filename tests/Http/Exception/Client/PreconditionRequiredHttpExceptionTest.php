<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\PreconditionRequiredHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class PreconditionRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PreconditionRequiredHttpException();
    }
}
