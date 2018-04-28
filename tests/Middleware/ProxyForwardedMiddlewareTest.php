<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Middleware\ProxyForwardedMiddleware;


use Chiron\Http\Response;
use Chiron\Middleware\ErrorHandlerMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
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


class ProxyForwardedMiddlewareTest extends TestCase
{
    public function testSchemeAndHostAndPortWithPortInHostHeader()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '192.168.0.1',
            'HTTP_HOST' => 'foo.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'example.com:1234',
        ]);
        $middleware = new ProxyForwardedMiddleware();

        $handler = function ($request) use (&$scheme, &$host, &$port) {
            // simply store the values
            $scheme = $request->getUri()->getScheme();
            $host = $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));
        $this->assertSame('https', $scheme);
        $this->assertSame('example.com', $host);
        $this->assertSame(1234, $port);
    }

    public function testSchemeAndHostAndPortWithPortInPortHeader()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '192.168.0.1',
            'HTTP_HOST' => 'foo.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'example.com',
            'HTTP_X_FORWARDED_PORT' => '1234',
        ]);
        $middleware = new ProxyForwardedMiddleware();

        $handler = function ($request) use (&$scheme, &$host, &$port) {
            // simply store the values
            $scheme = $request->getUri()->getScheme();
            $host = $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));
        $this->assertSame('https', $scheme);
        $this->assertSame('example.com', $host);
        $this->assertSame(1234, $port);
    }
    public function testSchemeAndHostAndPortWithPortInHostAndPortHeader()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '192.168.0.1',
            'HTTP_HOST' => 'foo.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'example.com:1000',
            'HTTP_X_FORWARDED_PORT' => '2000',
        ]);
        $middleware = new ProxyForwardedMiddleware();

        $handler = function ($request) use (&$scheme, &$host, &$port) {
            // simply store the values
            $scheme = $request->getUri()->getScheme();
            $host = $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));
        $this->assertSame('https', $scheme);
        $this->assertSame('example.com', $host);
        $this->assertSame(1000, $port);
    }
    public function testNonTrustedProxies()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_HOST' => 'foo.com',
            'HTTP_X_FORWARDED_HOST' => 'example.com:1234',
        ]);
        $middleware = new ProxyForwardedMiddleware(false);

        $handler = function ($request) use (&$scheme, &$host, &$port) {
            // simply store the values
            $scheme = $request->getUri()->getScheme();
            $host = $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));
        $this->assertSame('http', $scheme);
        $this->assertSame('foo.com', $host);
        $this->assertSame(null, $port);
    }

    /**
     * @dataProvider getLongHostNames
     */
    public function testVeryLongHosts($newHost)
    {
        $start = microtime(true);

        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            //'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_HOST' => 'foo.com',
            'HTTP_X_FORWARDED_HOST' => $newHost,
        ]);
        $middleware = new ProxyForwardedMiddleware();

        $handler = function ($request) use (&$host) {
            $host = $request->getUri()->getHost();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));

        $this->assertEquals('foo.com', $host);
        $this->assertLessThan(0.02, microtime(true) - $start);
    }
    /**
     * @dataProvider getHostValidities
     */
    public function testHostValidity($newHost, $isValid, $expectedHost = null, $expectedPort = null)
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
            //'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_HOST' => 'foo.com',
            //'HTTP_HOST' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80',
            'HTTP_X_FORWARDED_HOST' => $newHost,
        ]);
        $middleware = new ProxyForwardedMiddleware();

        $handler = function ($request) use (&$host, &$port) {
            $host = $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            return new Response();
        };

        $middleware->process($request, new HandlerProxy2($handler));

        if ($isValid) {
            $this->assertSame($expectedHost ?: $newHost, $host);
            if ($expectedPort) {
                $this->assertSame($expectedPort, $port);
            }
        } else {
            $this->assertSame('foo.com', $host);
        }
    }
    public function getHostValidities()
    {
        return array(
            array('.a', false),
            array('a..', false),
            array('a.', true),
            array("\xE9", false),
            array('localhost', true),
            array('localhost:8080', true, 'localhost', 8080),
            //@TODO : add a test for ipV6 host.
            array('[::1]', true),
            array('[::1]:8080', true, '[::1]', 8080),
            array(str_repeat('.', 101), false),
        );
    }
    public function getLongHostNames()
    {
        return array(
            array('a'.str_repeat('.abc:xyz', 1024 * 1024)),
            array(str_repeat(':', 101)),
        );
    }
}

