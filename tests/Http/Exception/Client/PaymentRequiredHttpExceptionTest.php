<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\PaymentRequiredHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class PaymentRequiredHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new PaymentRequiredHttpException();
    }
}
