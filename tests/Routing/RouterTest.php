<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Chiron\Routing\Strategy\StrategyInterface;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * Asserts that appropriately configured regex strings are added to patternMatchers.
     */
    public function testNewPatternMatchesCanBeAddedAtRuntime()
    {
        $router = new Router();
        $router->addPatternMatcher('mockMatcher', '[a-zA-Z]');
        $matchers = $this->getObjectAttribute($router, 'patternMatchers');
        $this->assertArrayHasKey('/{(.+?):mockMatcher}/', $matchers);
        $this->assertEquals('{$1:[a-zA-Z]}', $matchers['/{(.+?):mockMatcher}/']);
    }

    public function testGetSetBasePath()
    {
        $router = new Router();

        $this->assertSame('', $router->getBasePath());

        $router->setBasePath('/foo');

        $this->assertSame('/foo', $router->getBasePath());
    }

    public function httpMethods()
    {
        return [['get'], ['post'], ['put'], ['patch'], ['delete'], ['head'], ['options'], ['trace']];
    }

    /**
     * Asserts that the collection can map and return a route object.
     *
     * @dataProvider httpMethods
     */
    public function testRouteCollectionTraitHttpMethods($method)
    {
        $router = new Router();
        $path = '/something';
        $callable = function () {
        };

        $route = $router->{$method}($path, $callable);

        $this->assertSame([strtoupper($method)], $route->getAllowedMethods());
    }

    public function testRouteCollectionTraitMapAndAny()
    {
        $router = new Router();
        $path = '/something';
        $callable = function () {
        };

        $route_1 = $router->map($path, $callable);
        $this->assertSame(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'], $route_1->getAllowedMethods());

        $route_2 = $router->any($path, $callable);
        $this->assertSame(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'], $route_2->getAllowedMethods());
    }

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
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $router->get($routePath, 'handler')->name('foo');

        $routeResult = $router->match($request);

        $this->assertTrue($routeResult->isSuccess());
        $this->assertSame('foo', $routeResult->getMatchedRouteName());
        $this->assertSame(['id' => $expectedId], $routeResult->getMatchedParams());
    }

    /**
     * Base path is ignored by relativePathFor().
     */
    public function testRelativePathFor()
    {
        $router = new Router();

        $router->setBasePath('/base/path');
        $pattern = '/hello/{first:\w+}/{last}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $this->assertEquals(
            '/hello/josh/lockhart',
            $router->relativePathFor('foo', ['first' => 'josh', 'last' => 'lockhart'])
        );
    }

    public function testPathForWithNoBasePath()
    {
        $router = new Router();

        $router->setBasePath('');
        $pattern = '/hello/{first:\w+}/{last}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $this->assertEquals(
            '/hello/josh/lockhart',
            $router->pathFor('foo', ['first' => 'josh', 'last' => 'lockhart'])
        );
    }

    public function testPathForWithBasePath()
    {
        $router = new Router();

        $pattern = '/hello/{first:\w+}/{last}';

        $router->setBasePath('/base/path');
        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $this->assertEquals(
            '/base/path/hello/josh/lockhart',
            $router->pathFor('foo', ['first' => 'josh', 'last' => 'lockhart'])
        );
    }

    public function testPathForWithOptionalParameters()
    {
        $router = new Router();

        $pattern = '/archive/{year}[/{month:[\d:{2}]}[/d/{day}]]';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $this->assertEquals(
            '/archive/2015',
            $router->pathFor('foo', ['year' => '2015'])
        );
        $this->assertEquals(
            '/archive/2015/07',
            $router->pathFor('foo', ['year' => '2015', 'month' => '07'])
        );
        $this->assertEquals(
            '/archive/2015/07/d/19',
            $router->pathFor('foo', ['year' => '2015', 'month' => '07', 'day' => '19'])
        );
    }

    public function testPathForWithQueryParameters()
    {
        $router = new Router();

        $pattern = '/hello/{name}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $this->assertEquals(
            '/hello/josh?a=b&c=d',
            $router->pathFor('foo', ['name' => 'josh'], ['a' => 'b', 'c' => 'd'])
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing data for URL segment: first
     */
    public function testPathForWithMissingSegmentData()
    {
        $router = new Router();

        $pattern = '/hello/{first}/{last}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $router->pathFor('foo', ['last' => 'lockhart']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Named route does not exist for name:
     */
    public function testPathForRouteNotExists()
    {
        $router = new Router();

        $pattern = '/hello/{first}/{last}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $router->pathFor('bar', ['first' => 'josh', 'last' => 'lockhart']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Named route does not exist for name:
     */
    public function testRemoveRoute()
    {
        $router = new Router();

        $router->setBasePath('/base/path');
        $route1 = $router->map('/foo', 'callable');
        $route1->setName('foo');
        $route2 = $router->map('/bar', 'callable');
        $route2->setName('bar');
        $route3 = $router->map('/fizz', '$callable');
        $route3->setName('fizz');
        $route4 = $router->map('/buzz', '$callable');
        $route4->setName('buzz');
        $routeToRemove = $router->getNamedRoute('fizz');
        $routeCountBefore = count($router->getRoutes());
        $router->removeNamedRoute($routeToRemove->getName());
        $routeCountAfter = count($router->getRoutes());
        // Assert number of routes is now less by 1
        $this->assertEquals(
            ($routeCountBefore - 1),
            $routeCountAfter
        );
        // Simple test that the correct route was removed
        $this->assertEquals(
            $router->getNamedRoute('foo')->getName(),
            'foo'
        );
        $this->assertEquals(
            $router->getNamedRoute('bar')->getName(),
            'bar'
        );
        $this->assertEquals(
            $router->getNamedRoute('buzz')->getName(),
            'buzz'
        );
        // Exception thrown here, route no longer exists
        $router->getNamedRoute($routeToRemove->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Named route does not exist for name:
     */
    public function testRouteRemovalNotExists()
    {
        $router = new Router();

        $router->setBasePath('/base/path');
        $router->removeNamedRoute('non-existing-route-name');
    }

    /*
    public function testPathForWithModifiedRoutePattern()
    {
        $router = new Router();

        $router->setBasePath('/base/path');
        $pattern = '/hello/{first:\w+}/{last}';

        $route = $router->map($pattern, 'callable');
        $route->setName('foo');
        $route->setPattern('/hallo/{voornaam:\w+}/{achternaam}');
        $this->assertEquals(
            '/hallo/josh/lockhart',
            $router->relativePathFor('foo', ['voornaam' => 'josh', 'achternaam' => 'lockhart'])
        );
    }*/

    /**
     * @dataProvider provideMethodNotAllowedDispatchCases
     */
    public function testMethodNotAllowedDispatches($method, $uri, $callback, $availableMethods)
    {
        $request = new ServerRequest($method, new Uri($uri));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $callback($router);

        $routeResult = $router->match($request);

        $this->assertFalse($routeResult->isSuccess());
        $this->assertTrue($routeResult->isFailure());
        $this->assertTrue($routeResult->isMethodFailure());
    }

    public function provideMethodNotAllowedDispatchCases()
    {
        $cases = [];
        // 0 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/resource/123/456', 'handler0');
        };
        $method = 'POST';
        $uri = '/resource/123/456';
        $allowedMethods = ['GET'];
        $cases[] = [$method, $uri, $callback, $allowedMethods];
        // 1 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/resource/123/456', 'handler0');
            $r->post('/resource/123/456', 'handler1');
            $r->put('/resource/123/456', 'handler2');
            $r->map('/', 'handler3')->setAllowedMethods(['*']);
        };
        $method = 'DELETE';
        $uri = '/resource/123/456';
        $allowedMethods = ['GET', 'POST', 'PUT'];
        $cases[] = [$method, $uri, $callback, $allowedMethods];
        // 2 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/user/{name}/{id:[0-9]+}', 'handler0');
            $r->post('/user/{name}/{id:[0-9]+}', 'handler1');
            $r->put('/user/{name}/{id:[0-9]+}', 'handler2');
            $r->patch('/user/{name}/{id:[0-9]+}', 'handler3');
        };
        $method = 'DELETE';
        $uri = '/user/rdlowrey/42';
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH'];
        $cases[] = [$method, $uri, $callback, $allowedMethods];
        // 3 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->post('/user/{name}', 'handler1');
            $r->put('/user/{name:[a-z]+}', 'handler2');
            $r->patch('/user/{name:[a-z]+}', 'handler3');
        };
        $method = 'GET';
        $uri = '/user/rdlowrey';
        $allowedMethods = ['POST', 'PUT', 'PATCH'];
        $cases[] = [$method, $uri, $callback, $allowedMethods];
        // 4 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->map('/user', 'handlerGetPost')->setAllowedMethods(['GET', 'POST']);
            $r->delete('/user', 'handlerDelete');
        };
        $cases[] = ['PUT', '/user', $callback, ['GET', 'POST', 'DELETE']];
        // 5
        $callback = function (RouterInterface $r) {
            $r->post('/user.json', 'handler0');
            $r->get('/{entity}.json', 'handler1');
        };
        $cases[] = ['PUT', '/user.json', $callback, ['POST', 'GET']];
        // x -------------------------------------------------------------------------------------->
        return $cases;
    }

    /**
     * @dataProvider provideNotFoundDispatchCases
     */
    public function testNotFoundDispatches($method, $uri, $callback)
    {
        $request = new ServerRequest($method, new Uri($uri));

        $router = new Router();
        $strategyMock = $this->createMock(StrategyInterface::class);
        $router->setStrategy($strategyMock);

        $callback($router);

        $routeResult = $router->match($request);

        $this->assertFalse($routeResult->isSuccess());
        $this->assertTrue($routeResult->isFailure());
        $this->assertFalse($routeResult->isMethodFailure());
    }

    public function provideNotFoundDispatchCases()
    {
        $cases = [];
        // 0 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/resource/123/456', 'handler0');
        };
        $method = 'GET';
        $uri = '/not-found';
        $cases[] = [$method, $uri, $callback];
        // 1 -------------------------------------------------------------------------------------->
        // reuse callback from #0
        $method = 'POST';
        $uri = '/not-found';
        $cases[] = [$method, $uri, $callback];
        // 2 -------------------------------------------------------------------------------------->
        // reuse callback from #1
        $method = 'PUT';
        $uri = '/not-found';
        $cases[] = [$method, $uri, $callback];
        // 3 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/handler0', 'handler0');
            $r->get('/handler1', 'handler1');
            $r->get('/handler2', 'handler2');
        };
        $method = 'GET';
        $uri = '/not-found';
        $cases[] = [$method, $uri, $callback];
        // 4 -------------------------------------------------------------------------------------->
        $callback = function (RouterInterface $r) {
            $r->get('/user/{name}/{id:[0-9]+}', 'handler0');
            $r->get('/user/{id:[0-9]+}', 'handler1');
            $r->get('/user/{name}', 'handler2');
        };
        $method = 'GET';
        $uri = '/not-found';
        $cases[] = [$method, $uri, $callback];
        // 5 -------------------------------------------------------------------------------------->
        // reuse callback from #4
        $method = 'GET';
        $uri = '/user/rdlowrey/12345/not-found';
        $cases[] = [$method, $uri, $callback];
        // 6 -------------------------------------------------------------------------------------->
        // reuse callback from #5
        $method = 'HEAD';
        $cases[] = [$method, $uri, $callback];
        // x -------------------------------------------------------------------------------------->
        return $cases;
    }
}
