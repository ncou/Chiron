<?php

declare(strict_types=1);

namespace Tests\Handler\Reporter;

use Chiron\Handler\Reporter\LoggerReporter;
use Chiron\Http\Exception\Client\UnauthorizedHttpException;
use Chiron\Http\Exception\Server\InternalServerErrorHttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerReporterTest extends TestCase
{
    /**
     * @var LoggerReporter
     */
    protected $reporter;
    /**
     * @var Mockery\MockInterface
     */
    protected $logger;

    protected function setUp() {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->reporter = new LoggerReporter($this->logger);
    }

    public function testLogLevelThreshold()
    {
        $reporter = new LoggerReporter($this->logger);

        $this->assertAttributeEquals(LogLevel::DEBUG, 'logLevelThreshold' , $reporter);

        $reporter->setLogLevelThreshold(LogLevel::ERROR);

        $this->assertAttributeEquals(LogLevel::ERROR, 'logLevelThreshold' , $reporter);
    }

    public function testLogLevelThresholdInvalid_Constructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $reporter = new LoggerReporter($this->logger, 'foobar');
    }

    public function testLogLevelThresholdInvalid_Setter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $reporter = new LoggerReporter($this->logger);
        $reporter->setLogLevelThreshold('foobar');
    }

    public function testExceptionsAreLoggedCritical() {
        $this->logger->expects($this->once())->method('log')->with(LogLevel::CRITICAL, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));
        $this->reporter->report($request, new \Exception);
    }

    public function testErrorExceptionForCriticals() {
        $this->logger->expects($this->exactly(2))->method('log')->with(LogLevel::CRITICAL, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));

        $this->reporter->report($request, new \ErrorException('foobar', 0, E_ERROR));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_CORE_ERROR));
    }

    public function testErrorExceptionForErrors() {
        $this->logger->expects($this->exactly(2))->method('log')->with(LogLevel::ERROR, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));

        $this->reporter->report($request, new \ErrorException('foobar', 0, E_USER_ERROR));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_RECOVERABLE_ERROR));

    }
    public function testErrorExceptionForWarnings() {
        $this->logger->expects($this->exactly(4))->method('log')->with(LogLevel::WARNING, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));

        $this->reporter->report($request, new \ErrorException('foobar', 0, E_WARNING));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_USER_WARNING));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_CORE_WARNING));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_COMPILE_WARNING));
    }
    public function testErrorExceptionForNotices() {
        $this->logger->expects($this->exactly(5))->method('log')->with(LogLevel::NOTICE, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));

        $this->reporter->report($request, new \ErrorException('foobar', 0, E_NOTICE));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_USER_NOTICE));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_STRICT));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_DEPRECATED));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_USER_DEPRECATED));
    }
    public function testErrorExceptionForAlert() {
        $this->logger->expects($this->exactly(2))->method('log')->with(LogLevel::ALERT, $this->isType('string'));

        $request = new ServerRequest('GET', new Uri('/'));

        $this->reporter->report($request, new \ErrorException('foobar', 0, E_PARSE));
        $this->reporter->report($request, new \ErrorException('foobar', 0, E_COMPILE_ERROR));
    }

    public function testCanReport() {
        $reporter = new LoggerReporter($this->logger, LogLevel::NOTICE);

        $e = new \ErrorException('foobar', 0, E_USER_NOTICE);

        $this->assertTrue($reporter->canReport($e));

        $reporter->setLogLevelThreshold(LogLevel::WARNING);

        $this->assertFalse($reporter->canReport($e));

        $e = new \ErrorException('foobar', 0, E_USER_WARNING);

        $this->assertTrue($reporter->canReport($e));
    }

    public function testMessageFormat() {
        $request = new ServerRequest('GET', new Uri('/foobar/'));
        $request = $request->withHeader('Referer', '/baz/');

        $ePrevious = new \ErrorException('bar', 0, E_USER_NOTICE);
        $e = new \ErrorException('foo', 0, E_USER_NOTICE, __FILE__ ,  __LINE__, $ePrevious);

        $result = $this->invokeMethod($this->reporter, 'getMessage', [$request, $e]);

        $this->assertContains("[ErrorException] foo\n", $result);
        $this->assertContains("\nCaused by: [ErrorException] bar\n", $result);
        $this->assertContains("Request URL: /foobar/\n", $result);
        $this->assertContains("Referer URL: /baz/\n", $result);
    }

    /**
     * Invokes a inaccessible method.
     * @param $object
     * @param string $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     */
    private function invokeMethod($object, string $method, $args = [], bool $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }
        return $result;
    }
}
