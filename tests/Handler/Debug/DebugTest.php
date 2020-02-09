<?php

declare(strict_types=1);

namespace Tests\Handler\Reporter;

use Chiron\Handler\Reporter\LoggerReporter;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Chiron\Handler\Debug;

class DebugTest extends TestCase
{
   // dummy function to test trace in error handler.
    public static function triggerNotice($that)
    {
        $that->assertSame('', $foo.$foo.$bar);
    }
    public function testFailureCall()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('fopen(unknown.txt): failed to open stream: No such file or directory');
        Debug::call('fopen', 'unknown.txt', 'r');
    }
    public function testCallRestoreErrorHandler()
    {
        $prev = set_error_handler('var_dump');
        try {
            Debug::call('fopen', 'unknown.txt', 'r');
            $this->fail('An \ErrorException should have been raised');
        } catch (\ErrorException $e) {
            $prev = set_error_handler($prev);
            restore_error_handler();
        } finally {
            restore_error_handler();
        }
        $this->assertSame('var_dump', $prev);
    }
    public function testCallErrorExceptionInfo()
    {
        try {
            Debug::call([self::class, 'triggerNotice'], $this);
            $this->fail('An \ErrorException should have been raised');
        } catch (\ErrorException $e) {
            $trace = $e->getTrace();
            $this->assertSame(E_NOTICE, $e->getSeverity());
            $this->assertSame(__FILE__, $e->getFile());
            $this->assertSame('Undefined variable: foo', $e->getMessage());
            $this->assertSame(0, $e->getCode());
            $this->assertStringStartsWith('Chiron\Handler\{closure', $trace[0]['function']);
            $this->assertSame(Debug::class, $trace[0]['class']);
            $this->assertSame('triggerNotice', $trace[1]['function']);
            $this->assertSame(__CLASS__, $trace[1]['class']);
        }
    }
    public function testSuccessCall()
    {
        touch($filename = tempnam(sys_get_temp_dir(), 'sf_error_handler_'));
        self::assertIsResource(Debug::call('fopen', $filename, 'r'));
        unlink($filename);
    }

    public function testAnonymousClass()
    {
        $class = Debug::getClass(new class() extends \RuntimeException {
        });
        $this->assertSame('RuntimeException@anonymous', $class);

        $e = new \Exception(sprintf('Class "%s" blah.', \get_class(new class() extends \RuntimeException {
        })));
        $filtered = Debug::parseAnonymousClass($e->getMessage());
        $this->assertSame('Class "RuntimeException@anonymous" blah.', $filtered);
    }

    /**
     * @dataProvider provideTranslateException
     * @param string $expected_output
     * @param int    $exception_code
     */
    public function testTranslateException(string $expected_output, int $exception_code)
    {
        $output = Debug::translateErrorCode($exception_code);
        $this->assertEquals($expected_output, $output);
    }

    public function provideTranslateException()
    {
        return [
            // When passing an error constant value, ensure the error constant
            // is returned.
            ['E_USER_WARNING', E_USER_WARNING],

            // When passing a value not equal to an error constant, ensure
            // E_UNKNOWN is returned.
            ['E_UNKNOWN', 3],
        ];
    }

    public function testFormatExceptionPlain()
    {
        $msg = 'Sample exception message foo';
        $output = Debug::formatException(new \Exception($msg));
        $this->assertContains($msg, $output);
        $this->assertContains('[stacktrace]', $output);
    }

}
