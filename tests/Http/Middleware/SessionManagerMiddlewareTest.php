<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\SessionManagerMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Http\Session\SessionManager;
use Chiron\Tests\Utils\RequestHandlerCallable;
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

        $request = new ServerRequest('GET', new Uri('/'));
        $handler = function ($request) use (&$attribute) {
            $attribute = $request->getAttribute(SessionManager::class);

            return new Response();
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());

        $middleware->process($request, new RequestHandlerCallable($handler));

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
