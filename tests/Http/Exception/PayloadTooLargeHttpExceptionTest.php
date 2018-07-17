<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\PayloadTooLargeHttpException;

class PayloadTooLargeHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PayloadTooLargeHttpException();
    }
}
