<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function headerDataProvider()
    {
        return [
            [['X-Test' => 'Test']],
            [['X-Test' => 1]],
            [
                [
                    ['X-Test' => 'Test'],
                    ['X-Test-2' => 'Test-2'],
                ],
            ],
        ];
    }

    public function testHeadersDefault()
    {
        $exception = $this->createException();
        $this->assertSame([], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = $this->createException();
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }

    protected function createException()
    {
        return new HttpException(500);
    }
}
