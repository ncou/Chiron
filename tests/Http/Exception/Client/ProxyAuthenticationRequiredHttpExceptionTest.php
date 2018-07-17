<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\ProxyAuthenticationRequiredHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class ProxyAuthenticationRequiredHttpExceptionTest extends HttpExceptionTest
{
    public function testHeadersDefault()
    {
        $exception = new ProxyAuthenticationRequiredHttpException('Challenge');
        $this->assertSame(['Proxy-Authenticate' => 'Challenge'], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = new ProxyAuthenticationRequiredHttpException('Challenge');
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }
}
