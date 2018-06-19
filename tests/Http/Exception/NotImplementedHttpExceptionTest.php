<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\NotImplementedHttpException;

class NotImplementedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NotImplementedHttpException();
    }
}
