<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Routing\Strategy\StrategyInterface;
use PHPUnit\Framework\TestCase;

class FastRouterTest extends TestCase
{
    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot use the same placeholder "test" twice
     */
    public function testDuplicateVariableNameError()
    {
        $request = new ServerRequest('GET', new Uri('/foo'));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get('/foo/{test}/{test:\d+}', 'handler0');

        $routeResult = $router->match($request);
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot register two routes matching "/user/([^/]+)" for method "GET"
     */
    public function testDuplicateVariableRoute()
    {
        $request = new ServerRequest('GET', new Uri('/foo'));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get('/user/{id}', 'handler0'); // oops, forgot \d+ restriction ;)
        $router->get('/user/{name}', 'handler1');

        $routeResult = $router->match($request);
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot register two routes matching "/user" for method "GET"
     */
    public function testDuplicateStaticRoute()
    {
        $request = new ServerRequest('GET', new Uri('/foo'));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get('/user', 'handler0');
        $router->get('/user', 'handler1');

        $routeResult = $router->match($request);
    }

    /**
     * @codingStandardsIgnoreStart
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Static route "/user/nikic" is shadowed by previously defined variable route "/user/([^/]+)" for method "GET"
     * @codingStandardsIgnoreEnd
     */
    public function testShadowedStaticRoute()
    {
        $request = new ServerRequest('GET', new Uri('/foo'));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get('/user/{name}', 'handler0');
        $router->get('/user/nikic', 'handler1');

        $routeResult = $router->match($request);
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Regex "(en|de)" for parameter "lang" contains a capturing group
     */
    public function testCapturing()
    {
        $request = new ServerRequest('GET', new Uri('/foo'));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get('/{lang:(en|de)}', 'handler0');

        $routeResult = $router->match($request);
    }
}
