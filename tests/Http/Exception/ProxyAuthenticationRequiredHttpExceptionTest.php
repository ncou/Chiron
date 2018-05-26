<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\ProxyAuthenticationRequiredHttpException;

class ProxyAuthenticationRequiredHttpExceptionTest extends HttpExceptionTest
{
    public function testHeadersDefault()
    {
        $exception = new ProxyAuthenticationRequiredHttpException('Challenge');
        $this->assertSame(['Proxy-Authenticate' => 'Challenge'], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = new ProxyAuthenticationRequiredHttpException('Challenge');
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }
}
