<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionBasicTest extends TestCase
{
    public function testConstructorStatus()
    {
        $exception = new HttpException(500);
        $this->assertSame(500, $exception->getStatusCode());
    }

    public function testExceptionWithStatusCodeBelow400()
    {
        $this->expectException(\InvalidArgumentException::class);

        return new HttpException(399);
    }

    public function testExceptionWithStatusCodeAboveOrEquals600()
    {
        $this->expectException(\InvalidArgumentException::class);

        return new HttpException(600);
    }
}
