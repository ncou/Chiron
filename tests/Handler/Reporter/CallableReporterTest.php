<?php

declare(strict_types=1);

namespace Tests\Handler\Reporter;

use Chiron\Handler\Reporter\CallableReporter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class CallableReporterTest extends TestCase
{
    public function testCallableIsCalled()
    {
        $result = '';
        $callable = function (ServerRequestInterface $request, Throwable $e) use(&$result) {
            $result = $request->getRequestTarget().$e->getMessage();
        };

        $reporter = new CallableReporter($callable);
        $request = new ServerRequest('GET', new Uri('foo'));
        $e = new \Exception('bar');

        $this->assertTrue($reporter->canReport($e));

        $reporter->report($request, $e);

        $this->assertEquals('foobar', $result);
    }
}

