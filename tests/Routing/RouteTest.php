<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Routing\Route;
use Chiron\Routing\Strategy\StrategyInterface;
use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\RouteConditionHandlerInterface;
use Chiron\Routing\Traits\StrategyAwareInterface;
use InvalidArgumentException;
//use Psr\Http\Server\MiddlewareInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chiron\Routing\Route
 */
class RouteTest extends TestCase
{
    public function testConstructor()
    {
        // test with a callable for handler
        $callback = function () {
        };
        $route = new Route('/', $callback, 100);
        $this->assertSame($callback, $route->getHandler());

        $this->assertInstanceOf(RouteConditionHandlerInterface::class, $route);
        $this->assertInstanceOf(StrategyAwareInterface::class, $route);
        $this->assertInstanceOf(MiddlewareAwareInterface::class, $route);
    }

    public function testPath()
    {
        $route = new Route('/{bar}', 'handler', 0);
        $this->assertEquals('/{bar}', $route->getPath());
        $route = new Route('', 'handler', 0);
        $this->assertEquals('/', $route->getPath());
        $route = new Route('bar', 'handler', 0);
        $this->assertEquals('/bar', $route->getPath());
        $route = new Route('//path', 'handler', 0);
        $this->assertEquals('/path', $route->getPath());
    }

    // TODO : tester le getParentGroup et le setParentGroup
    //$this->assertEquals(null , $route->getParentGroup());

    public function testDefaultGetterSetter()
    {
        $route = new Route('/', 'foobar', 100);

        $this->assertEquals([], $route->gatherMiddlewareStack());

        $this->assertSame('foobar', $route->getHandler());
        $this->assertEquals('/', $route->getPath());
        $this->assertEquals('route_100', $route->getIdentifier());

        $this->assertEquals([], $route->getDefaults());

        $route->setDefaults(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $route->getDefaults());

        $route->addDefaults(['baz' => true, 'qux' => 0]);
        $this->assertEquals(['foo' => 'bar', 'baz' => true, 'qux' => 0], $route->getDefaults());

        $this->assertEquals(null, $route->getDefault('foobar'));
        $this->assertEquals('bar', $route->getDefault('foo'));
        $this->assertEquals(true, $route->getDefault('baz'));
        $this->assertEquals(0, $route->getDefault('qux'));

        $this->assertEquals(true, $route->hasDefault('foo'));
        $this->assertEquals(false, $route->hasDefault('foobar'));

        $route->value('foo', 'foo');
        $this->assertEquals('foo', $route->getDefault('foo'));

        $route->setDefault('foobar', 'foo');
        $this->assertEquals(true, $route->hasDefault('foobar'));
    }

    public function testRouteMiddlewareTrait()
    {
        $route = new Route('/', 'foobar', 100);

        $this->assertEquals([], $route->getMiddlewareStack());

        $route->middleware('baz');

        $this->assertEquals('baz', $route->getMiddlewareStack()[0]);

        $route->prependMiddleware('qux');

        $this->assertEquals('qux', $route->getMiddlewareStack()[0]);
    }

    public function testRouteConditionTrait()
    {
        $route = new Route('/', 'foobar', 100);

        $this->assertEquals(null, $route->getHost());
        $this->assertEquals(null, $route->getScheme());
        $this->assertEquals(null, $route->getPort());

        $route->setHost('host_1');
        $this->assertEquals('host_1', $route->getHost());

        $route->host('host_2');
        $this->assertEquals('host_2', $route->getHost());

        $route->setScheme('http');
        $this->assertEquals('http', $route->getScheme());

        $route->scheme('https');
        $this->assertEquals('https', $route->getScheme());

        $route->requireHttp();
        $this->assertEquals('http', $route->getScheme());

        $route->requireHttps();
        $this->assertEquals('https', $route->getScheme());

        $route->setPort(8080);
        $this->assertEquals(8080, $route->getPort());

        $route->port(8181);
        $this->assertEquals(8181, $route->getPort());
    }

    public function testRouteStrategyTrait()
    {
        $route = new Route('/', 'foobar', 100);

        $this->assertEquals(null, $route->getStrategy());

        $strategyMock = $this->createMock(StrategyInterface::class);
        $route->setStrategy($strategyMock);

        $this->assertEquals($strategyMock, $route->getStrategy());

        $route->strategy($strategyMock);

        $this->assertEquals($strategyMock, $route->getStrategy());
    }

    public function testRequirementGetterSetter()
    {
        $route = new Route('/', 'foobar', 0);
        $this->assertEquals([], $route->getRequirements());

        $route->setRequirements(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $route->getRequirements());

        $route->addRequirements(['baz' => 'qux']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $route->getRequirements());

        $this->assertEquals(null, $route->getRequirement('foobar'));
        $this->assertEquals('bar', $route->getRequirement('foo'));
        $this->assertEquals('qux', $route->getRequirement('baz'));

        $this->assertEquals(true, $route->hasRequirement('foo'));
        $this->assertEquals(false, $route->hasRequirement('foobar'));

        $route->assert('foo', 'foo');
        $this->assertEquals('foo', $route->getRequirement('foo'));

        $route->setRequirement('foobar', 'foo');
        $this->assertEquals(true, $route->hasRequirement('foobar'));
    }

    public function testNameGetterSetter()
    {
        $route = new Route('/', 'foobar', 0);
        $this->assertEquals(null, $route->getName());

        $route->name('foobar');
        $this->assertEquals('foobar', $route->getName());

        $route->setName('baz');
        $this->assertEquals('baz', $route->getName());
    }

    public function testMethodGetterSetter()
    {
        $route = new Route('/', 'foobar', 0);
        $this->assertEquals(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'], $route->getAllowedMethods());

        $route->method('GET');
        $this->assertEquals(['GET'], $route->getAllowedMethods());

        $route->method('post', 'put');
        $this->assertEquals(['POST', 'PUT'], $route->getAllowedMethods());

        $route->setAllowedMethods(['trace', 'patch']);
        $this->assertEquals(['TRACE', 'PATCH'], $route->getAllowedMethods());
    }

    public function testRequirementSanitize()
    {
        $route = new Route('/', 'foobar', 0);

        $route->setRequirements(['foo' => '^bar']);
        $this->assertEquals(['foo' => 'bar'], $route->getRequirements());

        $route->setRequirements(['foo' => 'bar$']);
        $this->assertEquals(['foo' => 'bar'], $route->getRequirements());

        $route->setRequirements(['foo' => '^bar$']);
        $this->assertEquals(['foo' => 'bar'], $route->getRequirements());
    }

    public function sanitizeInvalid()
    {
        return [
            [''],
            ['^'],
            ['$'],
            ['^$'],
        ];
    }

    /**
     * @dataProvider sanitizeInvalid
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Routing requirement for "foo" cannot be empty.
     */
    public function testRequirementSanitizeException($value)
    {
        $route = new Route('/', 'foobar', 0);

        $route->setRequirements(['foo' => $value]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage HTTP methods argument was empty; must contain at least one method
     */
    public function testMethodEmptyException()
    {
        $route = new Route('/', 'foobar', 0);

        $route->setAllowedMethods([]);
    }

    public function invalidHttpMethodsProvider()
    {
        return [
            [[123]],
            [[123, 456]],
            [['@@@']],
            [['@@@', '@@@']],
        ];
    }

    /**
     * @dataProvider invalidHttpMethodsProvider
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage One or more HTTP methods were invalid
     */
    public function testMethodInvalidException(array $invalidHttpMethods)
    {
        $route = new Route('/', 'foobar', 0);

        $route->setAllowedMethods($invalidHttpMethods);
    }
}
