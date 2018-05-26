<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Tests\Http;

use Chiron\Http\Factory\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testServerRequestIsMethod()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);

        $this->assertTrue($request->isMethod('GET'));
    }

    public function testServerRequestIsMethodCaseSensitive()
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GeT',
        ]);

        $this->assertTrue($request->isMethod('get'));
    }

    public function testServerRequestHasCookie()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GeT',
        ]);

        $this->assertTrue($request->hasCookie('foo'));
    }

    public function testServerRequestGetCookieParam()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GeT',
        ]);

        $this->assertSame('bar', $request->getCookieParam('foo'));
        $this->assertSame('bar', $request->getCookieParam('not_exist', 'bar'));
    }

    public function testServerRequestGetSchemeHTTPS_on()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
            'HTTPS'                  => 'on',
        ]);

        $this->assertSame('https', $request->getScheme());
        $this->assertTrue($request->isSecure());
    }

    public function testServerRequestGetSchemeHTTPS_off()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
            'HTTPS'                  => 'off',
        ]);

        $this->assertSame('http', $request->getScheme());
        $this->assertFalse($request->isSecure());
    }

    public function testServerRequestGetSchemeHTTPS()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
            'REQUEST_SCHEME'         => 'https',
        ]);

        $this->assertSame('https', $request->getScheme());
        $this->assertTrue($request->isSecure());
    }

    public function testServerRequestGetSchemeHTTP()
    {
        $_COOKIE['foo'] = 'bar';
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
            'REQUEST_SCHEME'         => 'http',
        ]);

        $this->assertSame('http', $request->getScheme());
        $this->assertFalse($request->isSecure());
    }
}
