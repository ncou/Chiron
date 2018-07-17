<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\InsufficientStorageHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class InsufficientStorageHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new InsufficientStorageHttpException();
    }
}
