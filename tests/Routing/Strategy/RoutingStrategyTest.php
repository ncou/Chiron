<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Strategy;

use Chiron\Application;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Http\Psr\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

// TODO : classe à finir de compléter !!!!!!!!!!

class RoutingStrategyTest extends TestCase
{
    public function testRouteStrategyWithoutRequestTypeHintting()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $routeCallback = function ($request) {
            $response = new Response();

            return $response->write('SUCCESS');
        };

        $app = new Application();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        $route = $app->get('/foo', $routeCallback);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('SUCCESS', (string) $response->getBody());
    }

    public function testRouteStrategyWithRequestTypeHintting()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $routeCallback = function (ServerRequestInterface $request) {
            $response = new Response();

            return $response->write('SUCCESS');
        };

        $app = new Application();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        $route = $app->get('/foo', $routeCallback);

        $response = $app->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('SUCCESS', (string) $response->getBody());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller "Closure" requires that you provide a value for the "$request" argument (because there is no default value or because there is a non optional argument after this one).
     */
    public function testRouteStrategyWithBadTypeHintting()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $routeCallback = function (int $request) {
            $response = new Response();

            return $response->write('SUCCESS');
        };

        $app = new Application();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        $route = $app->get('/foo', $routeCallback);

        $response = $app->process($request);
    }

    public function testRouteStrategyWithScalarTypeHintting()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo/123/bar/true/2.3',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $routeCallback = function (ServerRequestInterface $request, int $id, string $name, bool $isRegistered, float $floatNumber) {
            $response = new Response();

            return $response->write($id . $name . ($isRegistered ? 'true' : 'false') . $floatNumber);
        };

        $app = new Application();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        $route = $app->get('/foo/[:id]/[:name]/[:isRegistered]/[:floatNumber]', $routeCallback);

        $response = $app->process($request);

        $this->assertEquals('123bartrue2.3', (string) $response->getBody());
    }

    public function testRouteStrategyWithScalarTypeHinttingAndDefaultValue()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/foo/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $routeCallback = function (ServerRequestInterface $request, int $id = 123, string $name = 'bar', bool $isRegistered = true, float $floatNumber = 2.3) {
            $response = new Response();

            return $response->write($id . $name . ($isRegistered ? 'true' : 'false') . $floatNumber);
        };

        $app = new Application();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        $route = $app->get('/foo/[:id]?/[:name]?/[:isRegistered]?/[:floatNumber]?', $routeCallback);

        $response = $app->process($request);

        $this->assertEquals('123bartrue2.3', (string) $response->getBody());
    }
}
