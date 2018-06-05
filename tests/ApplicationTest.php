<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
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
        $app = new Application();
        $route = $app->get($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('GET', 'methods', $route);
    }

    public function testPostRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->post($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }

    public function testPutRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->put($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('PUT', 'methods', $route);
    }

    public function testPatchRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->patch($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('PATCH', 'methods', $route);
    }

    public function testDeleteRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->delete($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('DELETE', 'methods', $route);
    }

    public function testOptionsRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->options($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('OPTIONS', 'methods', $route);
    }

    public function testHeadRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->head($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('HEAD', 'methods', $route);
    }

    public function testAnyRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->any($path, $callable);
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('GET', 'methods', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
        $this->assertAttributeContains('PUT', 'methods', $route);
        $this->assertAttributeContains('PATCH', 'methods', $route);
        $this->assertAttributeContains('DELETE', 'methods', $route);
        $this->assertAttributeContains('OPTIONS', 'methods', $route);
    }

    public function testRouteRoute()
    {
        $path = '/foo';
        $callable = function ($req, $res) {
            // Do something
        };
        $app = new Application();
        $route = $app->map($path, $callable)->method('GET', 'POST');
        $this->assertInstanceOf('\Chiron\Routing\Route', $route);
        $this->assertAttributeContains('GET', 'methods', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }
}
