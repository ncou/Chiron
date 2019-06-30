<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
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
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->get($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('GET', 'methods', $route);
    }

    public function testPostRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->post($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }

    public function testPutRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->put($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('PUT', 'methods', $route);
    }

    public function testPatchRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->patch($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('PATCH', 'methods', $route);
    }

    public function testDeleteRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->delete($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('DELETE', 'methods', $route);
    }

    public function testOptionsRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->options($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('OPTIONS', 'methods', $route);
    }

    public function testHeadRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->head($path, $callable);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('HEAD', 'methods', $route);
    }

    public function testAnyRoute()
    {
        $path = '/foo';
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->any($path, $callable);
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
        $callable = function () {
            // Do something
        };
        $app = new Kernel();
        $collector = $app->getRouter()->getRouteCollector();
        $route = $collector->map($path, $callable)->method('GET', 'POST');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertAttributeContains('GET', 'methods', $route);
        $this->assertAttributeContains('POST', 'methods', $route);
    }

    public function testRouteRedirect()
    {
        $app = new Kernel();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        // TODO : il faut d'abord régler le fait que FastRoute ne supporte pas les routes en doublon avant de pouvoir décommenter ce bout de code !!!
        /*
                $route = $app->getRouter()->get('/contact_us', function () {
                    throw new \Exception('Route should not be reachable.');
                });
        */
        $app->getRouter()->getRouteCollector()->redirect('/contact_us', 'contact');

        $request = new ServerRequest('GET', new Uri('/contact_us'));
        $response = $app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('contact', $response->getHeaderLine('Location'));
    }

    public function testRouteRedirectWithCustomStatus()
    {
        $app = new Kernel();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        // TODO : il faut d'abord régler le fait que FastRoute ne supporte pas les routes en doublon avant de pouvoir décommenter ce bout de code !!!
        /*
                $route = $app->getRouter()->get('/contact_us', function () {
                    throw new \Exception('Route should not be reachable.');
                });
        */
        $app->getRouter()->getRouteCollector()->redirect('/contact_us', 'contact', 301);

        $request = new ServerRequest('GET', new Uri('/contact_us'));
        $response = $app->handle($request);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('contact', $response->getHeaderLine('Location'));
    }

    public function testRoutePermanentRedirect()
    {
        $app = new Kernel();
        $app->middleware([RoutingMiddleware::class, DispatcherMiddleware::class]);
        // TODO : il faut d'abord régler le fait que FastRoute ne supporte pas les routes en doublon avant de pouvoir décommenter ce bout de code !!!
        /*
                $route = $app->getRouter()->get('/contact_us', function () {
                    throw new \Exception('Route should not be reachable.');
                });
        */
        $app->getRouter()->getRouteCollector()->permanentRedirect('/contact_us', 'contact');

        $request = new ServerRequest('GET', new Uri('/contact_us'));
        $response = $app->handle($request);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('contact', $response->getHeaderLine('Location'));
    }
}
