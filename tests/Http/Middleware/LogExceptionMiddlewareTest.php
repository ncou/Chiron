<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\LogExceptionMiddleware;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class LogExceptionMiddlewareTest extends TestCase
{
    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
    }

    protected function tearDown()
    {
    }

    public function createMiddleware()
    {
        $loggerMiddleware = new LogExceptionMiddleware(new NullLogger());

        return $loggerMiddleware;
    }

    public function testReturnsResponseFromHandlerWhenNoProblemsOccur()
    {
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequestInterface::class))
            ->willReturn($expectedResponse);

        $middleware = $this->createMiddleware();

        $result = $middleware->process($this->request->reveal(), $handler->reveal());

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * @expectedException \ErrorException
     */
    public function testReturnsErrorResponseIfHandlerRaiseAnError()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequestInterface::class))
            ->willThrow(new ErrorException('Warning', 0, E_WARNING));

        $middleware = $this->createMiddleware();

        $result = $middleware->process($this->request->reveal(), $handler->reveal());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReturnsErrorResponseIfHandlerRaisesAnException()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequestInterface::class))
            ->willThrow(new RuntimeException('Exception raised'));

        $middleware = $this->createMiddleware();

        $result = $middleware->process($this->request->reveal(), $handler->reveal());
    }
}
