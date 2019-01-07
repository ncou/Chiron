<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Handler\ErrorHandler;
use Chiron\Http\Factory\ResponseFactory;
use Chiron\Handler\Error\ExceptionManager;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Tests\Utils\RequestHandlerCallable;
use Chiron\Tests\Utils\ExceptionHandlerCallable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;
use const E_USER_DEPRECATED;
use function error_reporting;
use function trigger_error;

class ErrorHandlerMiddlewareTest extends TestCase
{

    private $request;


    protected function setUp()
    {
        $this->request = new ServerRequest('GET', new Uri('/'));

        $this->errorReporting = error_reporting();
    }

    protected function tearDown()
    {
        error_reporting($this->errorReporting);
    }

    public function createMiddleware()
    {
        $debug = true;
        $middleware = new ErrorHandlerMiddleware($debug);
        $errorHandler = new ErrorHandler(new ResponseFactory());

        $middleware->bindHandler(Throwable::class, $errorHandler);

        return $middleware;
    }

    public function testReturnsResponseFromHandlerWhenNoProblemsOccur()
    {
        $handler = function ($request) {
            $response = new Response();
            $response->getBody()->write('success');
            return $response;
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals('success', (string) $response->getBody());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRethrowErrorIfHandlerIsNotDefined()
    {
        $handler = function ($request) {
            throw new RuntimeException('Exception raised');
        };

        $middleware = new ErrorHandlerMiddleware(true);

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));
    }

    public function testReturnsErrorResponseIfHandlerDoesNotReturnAResponse()
    {
        $handler = function ($request) {
            return null;
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains("error.status: 500\r\n", (string) $response->getBody());
    }

    public function testReturnsErrorResponseIfHandlerRaisesAnErrorInTheErrorMask()
    {
        error_reporting(E_USER_DEPRECATED);

        $handler = function ($request) {
            trigger_error('Deprecated', E_USER_DEPRECATED);
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains("error.status: 500\r\n", (string) $response->getBody());
    }

    public function testReturnsResponseFromHandlerWhenErrorRaisedIsNotInTheErrorMask()
    {
        $originalMask = error_reporting();
        error_reporting($originalMask & ~E_USER_DEPRECATED);

        $handler = function ($request) {
            $response = new Response();
            $response->getBody()->write('success');

            trigger_error('Deprecated', E_USER_DEPRECATED);

            return $response;
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals('success', (string) $response->getBody());
    }

    public function testReturnsErrorResponseIfHandlerRaisesAnException()
    {
        $handler = function ($request) {
            new RuntimeException('Exception raised');
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains("error.status: 500\r\n", (string) $response->getBody());
    }

}
