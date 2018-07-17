<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\ LoopDetectedHttpException;

class LoopDetectedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new  LoopDetectedHttpException();
    }
}