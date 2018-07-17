<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Client\NotAcceptableHttpException;

class NotAcceptableHttpExceptionTest extends HttpExceptionTest
{
    protected function createException()
    {
        return new NotAcceptableHttpException();
    }
}
