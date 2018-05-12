<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-router for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-router/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Chiron\Middleware\DispatcherMiddleware;
use Chiron\Routing\RouteResult;

class DispatchMiddlewareTest extends TestCase
{
    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;
    /** @var DispatchMiddleware */
    private $middleware;
    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;
    /** @var ResponseInterface|ObjectProphecy */
    private $response;
    public function setUp()
    {
        $this->response   = $this->prophesize(ResponseInterface::class)->reveal();
        $this->request    = $this->prophesize(ServerRequestInterface::class);
        $this->handler    = $this->prophesize(RequestHandlerInterface::class);
        $this->middleware = new DispatcherMiddleware();
    }
    public function testInvokesHandlerIfRequestDoesNotContainRouteResult()
    {
        $this->request->getAttribute(RouteResult::class, false)->willReturn(false);
        $this->handler->handle($this->request->reveal())->willReturn($this->response);
        $response = $this->middleware->process($this->request->reveal(), $this->handler->reveal());
        $this->assertSame($this->response, $response);
    }
    public function testInvokesRouteResultWhenPresent()
    {
        $this->handler->handle(Argument::any())->shouldNotBeCalled();
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult
            ->isFailure()
            ->willReturn(false);

        $routeHandler = $this->prophesize(RequestHandlerInterface::class);
        $routeHandler
            ->handle(
                Argument::that([$this->request, 'reveal'])
            )
            ->willReturn($this->response);

        $routeResult
            ->getMatchedRoute()
            ->willReturn($routeHandler);

        $this->request->getAttribute(RouteResult::class, false)->will([$routeResult, 'reveal']);
        $response = $this->middleware->process($this->request->reveal(), $this->handler->reveal());
        $this->assertSame($this->response, $response);
    }
}
