<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zend\Expressive\Router\RouteResult
 */
class RouterTest extends TestCase
{
    protected function setUp()
    {
        $this->noop = new HandlerProxy2(function () {
        });
    }

    /** @test */
    public function map_returns_a_route_object()
    {
        $router = new Router();
        $route = $router->map('/test/123', $this->noop)->method('GET');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getAllowedMethods());
        $this->assertSame('/test/123', $route->getUrl());
    }

    /** @test */
    public function map_accepts_lowercase_verbs()
    {
        $router = new Router();
        $route = $router->map('/test/123', $this->noop)->setAllowedMethods(['get', 'post', 'put', 'patch', 'delete', 'options']);
        $this->assertSame(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $route->getAllowedMethods());
    }

    /* @test */
    /*
    public function map_removes_trailing_slash_from_uri()
    {
        $router = new Router;
        $route = $router->map('/test/123/', $this->noop)->method('GET');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getAllowedMethods());
        $this->assertSame('/test/123', $route->getUrl());
    }*/
    /* @test */
    /*
    public function can_add_routes_in_a_group()
    {
        $request = new ServerRequest([], [], '/prefix/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group('prefix', function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = $router->match($request);
        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function can_add_routes_in_a_group_using_array_as_first_param()
    {
        $request = new ServerRequest([], [], '/prefix/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group(['prefix' => 'prefix'], function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = $router->match($request);
        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function can_add_routes_in_a_group_using_array_as_first_param_with_no_prefix()
    {
        $request = new ServerRequest([], [], '/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group([], function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = $router->match($request);
        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function group_prefixes_work_with_leading_slash()
    {
        $request = new ServerRequest([], [], '/prefix/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group('/prefix', function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = $router->match($request);
        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function group_prefixes_work_with_trailing_slash()
    {
        $request = new ServerRequest([], [], '/prefix/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group('prefix/', function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->get('all', function () {
                return 'abc123';
            });
        });
        $response = $router->match($request);
        $this->assertSame(1, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function can_add_routes_in_nested_groups()
    {
        $request = new ServerRequest([], [], '/prefix/prefix2/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group('prefix', function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->group('prefix2', function ($group) use (&$count) {
                $count++;
                $this->assertInstanceOf(RouteGroup::class, $group);
                $group->get('all', function () {
                    return 'abc123';
                });
            });
        });
        $response = $router->match($request);
        $this->assertSame(2, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
    /* @test */
    /*
    public function can_add_routes_in_nested_groups_with_array_syntax()
    {
        $request = new ServerRequest([], [], '/prefix/prefix2/all', 'GET');
        $router = new Router;
        $count = 0;
        $router->group(['prefix' => 'prefix'], function ($group) use (&$count) {
            $count++;
            $this->assertInstanceOf(RouteGroup::class, $group);
            $group->group(['prefix' => 'prefix2'], function ($group) use (&$count) {
                $count++;
                $this->assertInstanceOf(RouteGroup::class, $group);
                $group->get('all', function () {
                    return 'abc123';
                });
            });
        });
        $response = $router->match($request);
        $this->assertSame(2, $count);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->getContents());
    }*/
}
