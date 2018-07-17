<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\ForbiddenHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class ForbiddenHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new ForbiddenHttpException();
    }
}
