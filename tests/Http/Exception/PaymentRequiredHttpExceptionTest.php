<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\PaymentRequiredHttpException;

class PaymentRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PaymentRequiredHttpException();
    }
}
