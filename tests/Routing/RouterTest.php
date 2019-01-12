<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Routing\Route;
use Chiron\Routing\Router;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Routing\Strategy\ApplicationStrategy;

use Chiron\Routing\Traits\MiddlewareAwareInterface;

use Chiron\Routing\Traits\RouteConditionHandlerInterface;

use Chiron\Routing\Traits\StrategyAwareInterface;

use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Http\Factory\ResponseFactory;

use Chiron\Routing\Resolver\ControllerResolver;

/**
 * @covers \Chiron\Routing\Route
 */
class RouterTest extends TestCase
{
    public function matchWithUrlEncodedSpecialCharsDataProvider()
    {
        return [
            ['/foo/{id:.+}', '/foo/b%20ar', 'b ar'],
            ['/foo/{id:.+}', '/foo/b%2Fr', 'b/r'],
            ['/foo/{id:.+}', '/foo/bar-%E6%B8%AC%E8%A9%A6', 'bar-測試'],
            ['/foo/{id:bär}', '/foo/b%C3%A4r', 'bär'],
            ['/foo/{id:bär}', '/foo/bär', 'bär'],
        ];
    }

    /**
     * @dataProvider matchWithUrlEncodedSpecialCharsDataProvider
     *
     * @param string $routePath
     * @param string $requestPath
     * @param string $expectedId
     */
    public function testMatchWithUrlEncodedSpecialChars($routePath, $requestPath, $expectedId)
    {
        $request = new ServerRequest('GET', new Uri($requestPath));

        $router = new Router();
        $router->setStrategy(new ApplicationStrategy(new ResponseFactory(), new ControllerResolver()));

        $router->get($routePath, 'handler')->name('foo');

        $routeResult = $router->match($request);

        $this->assertTrue($routeResult->isSuccess());
        $this->assertSame('foo', $routeResult->getMatchedRouteName());
        $this->assertSame(['id' => $expectedId], $routeResult->getMatchedParams());
    }
}
