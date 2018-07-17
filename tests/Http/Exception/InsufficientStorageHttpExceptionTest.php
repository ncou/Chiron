<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\InsufficientStorageHttpException;

class InsufficientStorageHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new InsufficientStorageHttpException();
    }
}
