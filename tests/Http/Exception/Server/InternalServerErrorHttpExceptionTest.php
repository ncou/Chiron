<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;

class InternalServerErrorHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new InternalServerErrorHttpException();
    }
}
