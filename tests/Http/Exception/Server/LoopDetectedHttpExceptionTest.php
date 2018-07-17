<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Server;

use Chiron\Http\Exception\Server\LoopDetectedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class LoopDetectedHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new  LoopDetectedHttpException();
    }
}
