<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Resolver;

use Chiron\Tests\Routing\Resolver\Fixtures\CallCallableTest;
use Chiron\Tests\Routing\Resolver\Fixtures\CallableTest;
use Chiron\Tests\Routing\Resolver\Fixtures\StaticCallableTest;
use Chiron\Tests\Routing\Resolver\Fixtures\InvokableTest;
use Chiron\Tests\Routing\Resolver\Fixtures\RequestHandlerTest;
use Chiron\Container\Container;
use Chiron\Application;
use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Route;
use Chiron\Routing\Strategy\JsonStrategy;
use Chiron\Routing\Resolver\CallableResolver;
use Chiron\Http\Factory\ResponseFactory;
use JsonSerializable;
use stdClass;
use ArrayObject;

// TODO : class à finir de compléter => https://github.com/slimphp/Slim/blob/4.x/tests/CallableResolverTest.php

class CallableResolverTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        CallableTest::$CalledCount = 0;
        StaticCallableTest::$CalledCount = 0;
        InvokableTest::$CalledCount = 0;
        RequestHandlerTest::$CalledCount = 0;
        $this->container = new Container();
    }


    public function testClosure()
    {
        $test = function () {
            static $called_count = 0;
            return $called_count++;
        };
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve($test);
        $callable();
        $this->assertEquals(1, $callable());
    }

    public function testFunctionName()
    {
        // @codingStandardsIgnoreStart
        function testCallable()
        {
            static $called_count = 0;
            return $called_count++;
        };
        // @codingStandardsIgnoreEnd
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve(__NAMESPACE__ . '\testCallable');
        $callable();
        $this->assertEquals(1, $callable());
    }
    public function testObjMethodArray()
    {
        $obj = new CallableTest();
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve([$obj, 'toCall']);
        $callable();
        $this->assertEquals(1, CallableTest::$CalledCount);
    }

    public function testMethodArrayStatic()
    {
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve([StaticCallableTest::class, 'toStaticCall']);
        $callable();
        $this->assertEquals(1, StaticCallableTest::$CalledCount);
    }

    public function testMethodStringStatic()
    {
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve('Chiron\Tests\Routing\Resolver\Fixtures\StaticCallableTest::toStaticCall');
        $callable();
        $this->assertEquals(1, StaticCallableTest::$CalledCount);
    }



    public function testMethodStringStaticWithInstanciate()
    {
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve('Chiron\Tests\Routing\Resolver\Fixtures\StaticCallableTest@toStaticCall');
        $callable();
        $this->assertEquals(1, StaticCallableTest::$CalledCount);
    }




    public function testChironCallable()
    {
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve('Chiron\Tests\Routing\Resolver\Fixtures\CallableTest@toCall');
        $callable();
        $this->assertEquals(1, CallableTest::$CalledCount);
    }

    public function testContainer()
    {
        $this->container['callable_service'] = new CallableTest();
        $resolver = new CallableResolver($this->container);
        $callable = $resolver->resolve('callable_service@toCall');
        $callable();
        $this->assertEquals(1, CallableTest::$CalledCount);
    }

    public function testResolutionToAnInvokableClassInContainer()
    {
        $this->container['an_invokable'] = function ($c) {
            return new InvokableTest();
        };
        $resolver = new CallableResolver($this->container);
        $callable = $resolver->resolve('an_invokable');
        $callable();
        $this->assertEquals(1, InvokableTest::$CalledCount);
    }
    public function testResolutionToAnInvokableClass()
    {
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve('Chiron\Tests\Routing\Resolver\Fixtures\InvokableTest');
        $callable();
        $this->assertEquals(1, InvokableTest::$CalledCount);
    }

    public function testResolutionToAPsrRequestHandlerClass()
    {
        $request = new ServerRequest('GET', new Uri('/'));
        $resolver = new CallableResolver(); // No container injected
        $callable = $resolver->resolve(RequestHandlerTest::class);
        $callable($request);
        $this->assertEquals("1", RequestHandlerTest::$CalledCount);
    }

    public function testResolutionToAPsrRequestHandlerContainer()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $this->container['a_requesthandler'] = function ($c) {
            return new RequestHandlerTest();
        };
        $resolver = new CallableResolver($this->container); // No container injected
        $callable = $resolver->resolve('a_requesthandler');
        $callable($request);
        $this->assertEquals("1", RequestHandlerTest::$CalledCount);
    }
















    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not resolvable.
     */
    public function testMethodNotFoundThrowException()
    {
        $this->container['callable_service'] = new CallableTest();
        $resolver = new CallableResolver($this->container);
        $resolver->resolve('callable_service@noFound');
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Callable "noFound" does not exist
     */
    public function testFunctionNotFoundThrowException()
    {
        $resolver = new CallableResolver($this->container);
        $resolver->resolve('noFound');
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Callable "Unknown" does not exist
     */
    public function testClassNotFoundThrowException()
    {
        $resolver = new CallableResolver($this->container);
        $resolver->resolve('Unknown@notFound');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not resolvable.
     */
    public function testCallableClassNotFoundThrowException()
    {
        $resolver = new CallableResolver($this->container);
        $resolver->resolve(['Unknown', 'notFound']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage (null) is not resolvable.
     */
    public function testNullThrowException()
    {
        $resolver = new CallableResolver();
        $callable = $resolver->resolve(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage (123) is not resolvable.
     */
    public function testScalarThrowException()
    {
        $resolver = new CallableResolver();
        $callable = $resolver->resolve(123);
    }
}
