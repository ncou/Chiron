<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\GatewayTimeoutHttpException;

class GatewayTimeoutHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new GatewayTimeoutHttpException();
    }
}
