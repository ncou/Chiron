<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\MethodNotAllowedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class MethodNotAllowedHttpExceptionTest extends HttpExceptionTest
{
    /*
    protected function createException()
    {
        return new MethodNotAllowedHttpException();
    }*/

    public function testHeadersDefault()
    {
        $exception = new MethodNotAllowedHttpException(['GET', 'PUT']);
        $this->assertSame(['Allow' => 'GET, PUT'], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = new MethodNotAllowedHttpException(['GET']);
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }
}
