<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Application;
use Chiron\Kernel;
use Chiron\Routing\Route;
use PHPUnit\Framework\TestCase;

class ApplicationRouterTest extends TestCase
{
    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/
    public function testGetRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->get($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('GET', 'methods', $route);
    }

    public function testPostRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->post($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }

    public function testPutRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->put($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('PUT', 'methods', $route);
    }

    public function testPatchRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->patch($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('PATCH', 'methods', $route);
    }

    public function testDeleteRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->delete($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('DELETE', 'methods', $route);
    }

    public function testOptionsRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->options($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('OPTIONS', 'methods', $route);
    }

    public function testHeadRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->head($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('HEAD', 'methods', $route);
    }

    public function testAnyRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->any($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('GET', 'methods', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
        $this->assertAttributeContains('PUT', 'methods', $route);
        $this->assertAttributeContains('PATCH', 'methods', $route);
        $this->assertAttributeContains('DELETE', 'methods', $route);
        $this->assertAttributeContains('OPTIONS', 'methods', $route);
    }

    public function testRouteMapping()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application(new Kernel());
        $route = $app->router->map($path, $callable)->method('GET', 'POST');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('GET', 'methods', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }
}
