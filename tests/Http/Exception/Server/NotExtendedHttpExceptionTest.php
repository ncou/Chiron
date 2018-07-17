<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\NotExtendedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class NotExtendedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NotExtendedHttpException();
    }
}
