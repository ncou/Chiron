<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing;

use Chiron\Routing\Route;
use InvalidArgumentException;
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

        $this->assertEquals('/', $route->getPath());
        $this->assertSame($callback, $route->getHandler());
        $this->assertEquals('route_100', $route->getIdentifier());

        // test with a string for handler
        $route = new Route('/', 'foobar', 100);
        $this->assertSame('foobar', $route->getHandler());
    }

    // TODO : tester le getParentGroup et le setParentGroup
    //$this->assertEquals(null , $route->getParentGroup());

    public function testDefaultGetterSetter()
    {
        $route = new Route('/', 'foobar', 0);
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
        $this->assertEquals([], $route->getAllowedMethods());

        $route->method('GET');
        $this->assertEquals(['GET'], $route->getAllowedMethods());

        $route->method('post', 'put');
        $this->assertEquals(['POST', 'PUT'], $route->getAllowedMethods());

        $route->setAllowedMethods(['TRACE', 'PATCH']);
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage One or more HTTP methods were invalid
     */
    public function testMethodInvalidStringException()
    {
        $route = new Route('/', 'foobar', 0);

        $route->setAllowedMethods(['POST', '=', 'GET']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage One or more HTTP methods were invalid
     */
    public function testMethodInvalidFormatException()
    {
        $route = new Route('/', 'foobar', 0);

        $route->setAllowedMethods(['POST', 0, 'GET']);
    }
}
