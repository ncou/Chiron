<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\NetworkAuthenticationRequiredHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class NetworkAuthenticationRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NetworkAuthenticationRequiredHttpException();
    }
}
