<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Server\BadGatewayHttpException;

class BadGatewayHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new BadGatewayHttpException();
    }
}
