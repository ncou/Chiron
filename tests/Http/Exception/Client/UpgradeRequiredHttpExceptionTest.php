<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception\Client;

use Chiron\Tests\Http\Exception\HttpExceptionTest;
use Chiron\Http\Exception\Client\UpgradeRequiredHttpException;

class UpgradeRequiredHttpExceptionTest extends HttpExceptionTest
{
    public function testHeadersDefault()
    {
        $exception = new UpgradeRequiredHttpException('Upgrade-Value');
        $this->assertSame(['Upgrade' => 'Upgrade-Value'], $exception->getHeaders());
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeadersSetter($headers)
    {
        $exception = new UpgradeRequiredHttpException('Upgrade-Value');
        $exception->setHeaders($headers);
        $this->assertSame($headers, $exception->getHeaders());
    }
}
