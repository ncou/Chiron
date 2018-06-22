<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\SessionManagerMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class SessionManagerMiddlewareTest extends TestCase
{
    protected function setUp()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefaults()
    {
        $middleware = new SessionManagerMiddleware();

        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) use (&$attributeFound) {
            $attributeFound = $request->getAttribute('Chiron::SESSION');

            return new Response();
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $middleware->process($request, new HandlerProxy2($handler));

        /*@$session->start(); // silence cookie warning
        $expected = [
            'lifetime' => 7200,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
        ];
        $this->assertEquals($expected, session_get_cookie_params());
        */

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('PHPSESSID', session_name());
        $this->assertNotNull($attributeFound);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomSessionNameAndAttributeName()
    {
        $middleware = new SessionManagerMiddleware();

        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) use (&$attributeFound) {
            $attributeFound = $request->getAttribute('ATTRIBUTE_SESSION');

            return new Response();
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $middleware->name('NEWSESSIONNAME')->attribute('ATTRIBUTE_SESSION');
        $middleware->process($request, new HandlerProxy2($handler));

        /*@$session->start(); // silence cookie warning
        $expected = [
            'lifetime' => 7200,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
        ];
        $this->assertEquals($expected, session_get_cookie_params());
        */

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('NEWSESSIONNAME', session_name());
        $this->assertNotNull($attributeFound);
    }
}
