<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Exception;

use Chiron\Http\Exception\UpgradeRequiredHttpException;

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