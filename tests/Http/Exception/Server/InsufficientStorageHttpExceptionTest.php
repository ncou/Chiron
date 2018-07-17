<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Server\InsufficientStorageHttpException;

class InsufficientStorageHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new InsufficientStorageHttpException();
    }
}
