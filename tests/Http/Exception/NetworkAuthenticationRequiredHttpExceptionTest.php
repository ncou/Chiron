<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\NetworkAuthenticationRequiredHttpException;

class NetworkAuthenticationRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NetworkAuthenticationRequiredHttpException();
    }
}
