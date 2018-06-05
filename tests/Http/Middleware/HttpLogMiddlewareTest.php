<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\HttpLogMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Logger;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class HttpLogMiddlewareTest extends TestCase
{
    /**
     * @var HttpLogMiddleware
     */
    protected $middleware;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $logger = new Logger('http_log.log', LogLevel::INFO);
        $this->middleware = new HttpLogMiddleware($logger, [
            'log_request'  => true,
            'log_response' => true,
        ]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testProcess()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            return new Response();
        };
        $this->middleware->process($request, new HandlerProxy2($handler));
        //$log = $this->root->getChild('http_log.log')->getContent();
        $log = file_get_contents('http_log.log');
        $this->assertNotFalse(strpos($log, 'Request: GET /'));
        $this->assertNotFalse(strpos($log, 'Response: 200 OK'));
    }
}
