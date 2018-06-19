<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\BadGatewayHttpException;

class BadGatewayHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new BadGatewayHttpException();
    }
}
