<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\NotExtendedHttpException;

class NotExtendedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NotExtendedHttpException();
    }
}
