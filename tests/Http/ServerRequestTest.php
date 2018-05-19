<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Tests\Http\Response;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use PHPUnit\Framework\TestCase;

use Psr\Http\Message\StreamInterface;

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
}
