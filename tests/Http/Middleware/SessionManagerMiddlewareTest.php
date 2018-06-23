<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Middleware\SessionManagerMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Session\SessionManager;
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
        $handler = function ($request) use (&$attribute) {
            $attribute = $request->getAttribute(SessionManager::class);

            return new Response();
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());

        $middleware->process($request, new HandlerProxy2($handler));

        $expected = [
            'lifetime' => 1800,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,
            'httponly' => true,
        ];
        $this->assertEquals($expected, session_get_cookie_params());
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('CHRSESSIONID', session_name());
        $this->assertNotNull($attribute);
        $this->assertInstanceof(SessionManager::class, $attribute);
    }
}
