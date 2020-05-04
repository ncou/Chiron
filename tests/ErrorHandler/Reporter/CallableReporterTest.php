<?php

declare(strict_types=1);

namespace Tests\ErrorHandler\Reporter;

use Chiron\ErrorHandler\Reporter\CallableReporter;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class CallableReporterTest extends TestCase
{
    public function testCallableIsCalled()
    {
        $result = '';
        $callable = function (ServerRequestInterface $request, Throwable $e) use (&$result) {
            $result = $request->getRequestTarget() . $e->getMessage();
        };

        $reporter = new CallableReporter($callable);
        $request = new ServerRequest('GET', new Uri('foo'));
        $e = new \Exception('bar');

        $this->assertTrue($reporter->canReport($e));

        $reporter->report($request, $e);

        $this->assertEquals('foobar', $result);
    }
}
