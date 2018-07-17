<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\PayloadTooLargeHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class PayloadTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PayloadTooLargeHttpException();
    }
}
