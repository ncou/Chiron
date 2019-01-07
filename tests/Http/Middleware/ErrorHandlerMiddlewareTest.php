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
use InvalidArgumentException;
use Throwable;
use const E_USER_DEPRECATED;
use function error_reporting;
use function trigger_error;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Psr\Http\Message\ResponseFactoryInterface;
use Error;
use Exception;

class ErrorHandlerMiddlewareTest extends TestCase
{
    private $errorReporting;

    private $captureFile;
    private $errorLog;

    private $request;


    protected function setUp()
    {
        $this->request = new ServerRequest('GET', new Uri('/'));

        $this->errorReporting = error_reporting();

        // prevent the error_log function in the exception handler to show the message in the console (as phpunit is run in cli mode)
        $this->captureFile = tmpfile();
        $this->errorLog = ini_set('error_log', stream_get_meta_data($this->captureFile)['uri']);
    }

    protected function tearDown()
    {
        error_reporting($this->errorReporting);
        ini_set('error_log', $this->errorLog);
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
     * @expectedException RuntimeException
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
        $this->assertContains("error.status: 500", (string) $response->getBody());
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
        $this->assertContains("error.status: 500", (string) $response->getBody());
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

    public function testReturnsErrorResponseIfHandlerRaisesAnPhpException()
    {
        $handler = function ($request) {
            throw new RuntimeException('Exception raised');
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains("error.status: 500", (string) $response->getBody());
    }

    public function testReturnsErrorResponseIfHandlerRaisesAnHttpException()
    {
        $handler = function ($request) {
            throw new BadRequestHttpException();
        };

        $middleware = $this->createMiddleware();

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains("error.status: 400", (string) $response->getBody());
    }

    public function testTheSameHandlerIsBindedOnlyOnce()
    {
        $middleware = $middleware = new ErrorHandlerMiddleware(true);

        // at the creation the handlers array is empty
        $this->assertAttributeCount(0, 'handlers', $middleware);

        $errorHandler = new ErrorHandler(new ResponseFactory());

        $middleware->bindHandler(Throwable::class, $errorHandler);
        $middleware->bindHandler(Throwable::class, $errorHandler);
        $middleware->bindHandler(Throwable::class, $errorHandler);

        // check multiple binding on the same class name
        $this->assertAttributeCount(1, 'handlers', $middleware);

        $middleware->bindHandler(Error::class, $errorHandler);
        $middleware->bindHandler(Exception::class, $errorHandler);
        $middleware->bindHandler(InvalidArgumentException::class, $errorHandler);
        $middleware->bindHandler(HttpException::class, $errorHandler);
        $middleware->bindHandler(BadRequestHttpException::class, $errorHandler);

        $this->assertAttributeCount(6, 'handlers', $middleware);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidHandlerName()
    {
        $middleware = $middleware = new ErrorHandlerMiddleware(true);
        $errorHandler = new ErrorHandler(new ResponseFactory());

        $middleware->bindHandler('/Foo/bar', $errorHandler);
    }

    public function testInternalErrorInTheHandlerWithDebugEnabled()
    {
        $debug = true;
        $middleware = $middleware = new ErrorHandlerMiddleware($debug);

        $factory =$this->prophesize(ResponseFactoryInterface::class);
        $factory->createResponse(Argument::type('int'))
                ->willThrow(new RuntimeException('Exception internal'));

        $errorHandler = new ErrorHandler($factory->reveal());

        $middleware->bindHandler(Throwable::class, $errorHandler);

        $handler = function ($request) {
            throw new BadRequestHttpException();
        };

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertContains("<pre>An Error occurred while handling another error:\nRuntimeException: Exception internal", (string) $response->getBody());
        $this->assertContains("Previous exception:\nChiron\Http\Exception\Client\BadRequestHttpException", (string) $response->getBody());
        $this->assertNotContains("\nRequest Details:", (string) $response->getBody());

        $contentOferrorLog = stream_get_contents($this->captureFile);
        $this->assertContains("\nRequest Details:", $contentOferrorLog);
    }

    public function testInternalErrorInTheHandlerWithDebugDisabled()
    {
        $debug = false;
        $middleware = $middleware = new ErrorHandlerMiddleware($debug);

        $factory =$this->prophesize(ResponseFactoryInterface::class);
        $factory->createResponse(Argument::type('int'))
                ->willThrow(new RuntimeException('Exception internal'));

        $errorHandler = new ErrorHandler($factory->reveal());

        $middleware->bindHandler(Throwable::class, $errorHandler);

        $handler = function ($request) {
            throw new BadRequestHttpException();
        };

        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals("An internal server error occurred.", (string) $response->getBody());
        $this->assertNotContains("\nRequest Details:", (string) $response->getBody());

        $contentOferrorLog = stream_get_contents($this->captureFile);
        $this->assertContains("\nRequest Details:", $contentOferrorLog);
    }

}
