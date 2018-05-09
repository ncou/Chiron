<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\CookiesManager;
use Chiron\CryptManager;
use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Middleware\EncryptCookiesMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class EncryptCookiesMiddlewareTest extends TestCase
{
    private $middleware;

    private $crypter;

    private function getCookieEncryptionKey()
    {
        return 'super secret key that no one can guess';
    }

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->crypter = new CryptManager();
        $this->middleware = new EncryptCookiesMiddleware(
            $this->getCookieEncryptionKey(),
            ['plain']
        );
    }

    /**
     * Test decoding request cookies.
     */
    public function testDecodeRequestCookies()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $request = $request->withCookieParams([
            'plain'  => 'always plain',
            'secret' => $this->crypter->encrypt('decoded', $this->getCookieEncryptionKey()),
        ]);
        $handler = function ($request) {
            $response = new Response();
            $this->assertSame('decoded', $request->getCookieParam('secret'));
            $this->assertSame('always plain', $request->getCookieParam('plain'));

            return $response->withHeader('called', 'yes');
        };
        $middleware = $this->middleware;
        $response = $middleware->process($request, new HandlerProxy2($handler));
        $this->assertSame('yes', $response->getHeaderLine('called'), 'Inner middleware not invoked');
    }

    /**
     * Test encoding cookies in the set-cookie header.
     */
    public function testEncodeResponseSetCookieHeader()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            $response = new Response();

            return $response->withAddedHeader('Set-Cookie', 'secret=be%20quiet')
                ->withAddedHeader('Set-Cookie', 'plain=in%20clear')
                ->withAddedHeader('Set-Cookie', 'ninja=shuriken');
        };
        $middleware = $this->middleware;
        $response = $middleware->process($request, new HandlerProxy2($handler));
        $this->assertNotContains('ninja=shuriken', $response->getHeaderLine('Set-Cookie'));
        $this->assertContains('plain=in%20clear', $response->getHeaderLine('Set-Cookie'));
        $cookies = CookiesManager::parseSetCookieHeader($response->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['ninja']));
        $this->assertEquals(
            'shuriken',
            $this->crypter->decrypt($cookies['ninja']['value'], $this->getCookieEncryptionKey())
        );
    }

    /*
     * Test encoding cookies in the cookie collection.
     *
     * @return void
     */
    /*
    public function testEncodeResponseCookieData()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $handler = function ($request) {
            $response = new Response();
            return $response->withCookie('secret', 'be quiet')
                ->withCookie('plain', 'in clear')
                ->withCookie('ninja', 'shuriken');
        };
        $middleware = $this->middleware;
        $response = $middleware->process($request, new HandlerProxy2($handler));
        $this->assertNotSame('shuriken', $response->getCookieParam('ninja'));
        $this->assertEquals(
            'shuriken',
            $this->crypter->decrypt($response->getCookieParam('ninja')['value'], $this->getCookieEncryptionKey())
        );
    }*/
}
