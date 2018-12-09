<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use Chiron\Http\Middleware\CheckMaintenanceMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;

class CheckMaintenanceMiddlewareTest extends TestCase
{
    protected $middleware;

    public $request;

    protected function setUp()
    {
        parent::setUp();
        $this->middleware = new CheckMaintenanceMiddleware();
        $this->request = new ServerRequest('GET', new Uri('/'));
    }

    public function retryAfterProvider(): array
    {
        return [
            [120, '120'],
            [new \DateTime('2016/12/12 10:10:30'), 'Mon, 12 Dec 2016 10:10:30 GMT'],
            [new \DateTimeImmutable('2016/12/12 10:10:30'), 'Mon, 12 Dec 2016 10:10:30 GMT'],
            ['Mon, 12 Dec 2016 10:10:30 GMT', 'Mon, 12 Dec 2016 10:10:30 GMT'],
        ];
    }

    public function testShutdownDefaultConstructor()
    {
        $handler = function ($request) {
            return new Response();
        };
        $middleware = $this->middleware;

        $result = $middleware->process($this->request, new RequestHandlerCallable($handler));
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testShutdown()
    {
        $handler = function ($request) {
            return new Response();
        };
        $middleware = $this->middleware;
        $middleware->isDownForMaintenance(true);

        try {
            $middleware->process($this->request, new RequestHandlerCallable($handler));
        } catch (ServiceUnavailableHttpException $e) {
            $this->assertEquals(503, $e->getStatusCode());
            $this->assertFalse(isset($e->getHeaders()['Retry-After']));
        }
    }

    /**
     * @dataProvider retryAfterProvider
     *
     * @param int|DateTimeInterface $duration
     */
    public function testRetryAfter($duration, string $header)
    {
        $handler = function ($request) {
            return new Response();
        };
        $middleware = $this->middleware;
        $middleware->isDownForMaintenance(true);
        $middleware->retryAfter($duration);

        try {
            $middleware->process($this->request, new RequestHandlerCallable($handler));
        } catch (ServiceUnavailableHttpException $e) {
            $this->assertEquals(503, $e->getStatusCode());
            $this->assertTrue(isset($e->getHeaders()['Retry-After']));
            $this->assertEquals($header, $e->getHeaders()['Retry-After']);
        }
    }
}
