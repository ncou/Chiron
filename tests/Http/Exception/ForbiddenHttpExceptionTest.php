<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\ForbiddenHttpException;

class ForbiddenHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new ForbiddenHttpException();
    }
}
