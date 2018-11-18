<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Strategy;

use Chiron\Application;
use Chiron\Http\Middleware\DispatcherMiddleware;
use Chiron\Http\Middleware\RoutingMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Route;
use Chiron\Routing\Strategy\JsonInvocationStrategy;
use Chiron\Routing\Strategy\CallableResolver;
use JsonSerializable;
use stdClass;
use ArrayObject;

// TODO : classe à finir de compléter !!!!!!!!!!

class JsonInvocationStrategyTest extends TestCase
{
    public function testJsonStrategyInitialisation()
    {
        $strategy = new JsonInvocationStrategy(new CallableResolver());

        $data = ['foo' => 'bar'];
        $callback =  function (ServerRequestInterface $request) use ($data) {
            return $data;
        };

        $route = new Route('/', $callback, 0);
        $request = new ServerRequest('GET', new Uri('/'));

        $response = $strategy->invokeRouteCallable($route, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals(json_encode($data, JsonInvocationStrategy::DEFAULT_JSON_FLAGS), (string) $response->getBody());

        $this->assertEquals(79, $strategy->getEncodingOptions());
    }


    /**
     * @param mixed $data
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider jsonErrorDataProvider
     */
    public function testInvalidArgumentExceptionOnJsonError($data)
    {
        $strategy = new JsonInvocationStrategy(new CallableResolver());

        $callback =  function (ServerRequestInterface $request) use ($data) {
            return $data;
        };

        $route = new Route('/', $callback, 0);
        $request = new ServerRequest('GET', new Uri('/'));

        $response = $strategy->invokeRouteCallable($route, $request);
    }
    /**
     * @param mixed $data
     *
     * @dataProvider jsonErrorDataProvider
     */
    public function testGracefullyHandledSomeJsonErrorsWithPartialOutputOnError($data)
    {
        $strategy = new JsonInvocationStrategy(new CallableResolver());
        $strategy->setEncodingOptions($strategy->getEncodingOptions() | JSON_PARTIAL_OUTPUT_ON_ERROR);

        $callback =  function (ServerRequestInterface $request) use ($data) {
            return $data;
        };

        $route = new Route('/', $callback, 0);
        $request = new ServerRequest('GET', new Uri('/'));

        $response = $strategy->invokeRouteCallable($route, $request);

        $this->assertInstanceOf(Response::class, $response);
    }
    /**
     * @return array
     */
    public function jsonErrorDataProvider()
    {
        // Resources can't be encoded
        $resource = tmpfile();
        // Recursion can't be encoded
        $recursiveObject = new stdClass;
        $objectB = new stdClass;
        $recursiveObject->b = $objectB;
        $objectB->a = $recursiveObject;
        // NAN or INF can't be encoded
        $nan = NAN;
        return [
            [$resource],
            [$recursiveObject],
            [$nan],
        ];
    }


    /**
     * @dataProvider setAndRetrieveDataProvider
     *
     * @param  $data
     */
    public function testSetAndRetrieveData($data): void
    {
        $strategy = new JsonInvocationStrategy(new CallableResolver());

        $callback =  function (ServerRequestInterface $request) use ($data) {
            return $data;
        };

        $route = new Route('/', $callback, 0);
        $request = new ServerRequest('GET', new Uri('/'));

        $response = $strategy->invokeRouteCallable($route, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(json_encode($data, JsonInvocationStrategy::DEFAULT_JSON_FLAGS), (string) $response->getBody());
    }

    public function setAndRetrieveDataProvider(): array
    {
        return [
            'JsonSerializable data' => [new JsonSerializableObject],
            'Array data' => [['foo' => 'bar']],
            'ArrayObject data' => [new ArrayObject(['foo' => 'bar'])],
        ];
    }

}


class JsonSerializableObject implements JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}
