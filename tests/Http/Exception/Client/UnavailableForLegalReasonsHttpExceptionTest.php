<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\UnavailableForLegalReasonsHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class UnavailableForLegalReasonsHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new UnavailableForLegalReasonsHttpException();
    }
}
