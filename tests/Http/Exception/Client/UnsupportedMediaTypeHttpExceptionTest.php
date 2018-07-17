<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\UnsupportedMediaTypeHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class UnsupportedMediaTypeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new UnsupportedMediaTypeHttpException();
    }
}
