<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\MisdirectedRequestHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class MisdirectedRequestHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new MisdirectedRequestHttpException();
    }
}
