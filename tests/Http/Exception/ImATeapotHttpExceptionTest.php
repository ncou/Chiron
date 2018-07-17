<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\ImATeapotHttpException;

class ImATeapotHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new ImATeapotHttpException();
    }
}
