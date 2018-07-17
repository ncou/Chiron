<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\BadGatewayHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class BadGatewayHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new BadGatewayHttpException();
    }
}
