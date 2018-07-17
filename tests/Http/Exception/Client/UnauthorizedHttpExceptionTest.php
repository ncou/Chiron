<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Tests\Http\Exception\HttpExceptionTest;

class UnauthorizedHttpExceptionTest extends HttpExceptionTest
{
    public function testHeadersDefault()
    {
        $exception = new UnauthorizedHttpException('Challenge');
        $this->assertSame(['WWW-Authenticate' => 'Challenge'], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = new UnauthorizedHttpException('Challenge');
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }
}
