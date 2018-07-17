<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\MisdirectedRequestHttpException;

class MisdirectedRequestHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new MisdirectedRequestHttpException();
    }
}
