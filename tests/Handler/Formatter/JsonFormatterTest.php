<?php

declare(strict_types=1);

namespace Tests\Handler\Formatter;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\JsonFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;

class JsonFormatterTest extends TestCase
{
    private $info;

    protected function setUp()
    {
        $this->info = new ExceptionInfo(__DIR__.'/../../../resources/lang/en/errors.json');
    }

    public function testFormatServerError()
    {
        $formatter = new JsonFormatter($this->info);
        $formated = $formatter->format(new HttpException(500, 'Gutted!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/500-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testFormatClientError()
    {
        $formatter = new JsonFormatter($this->info);
        $formated = $formatter->format(new HttpException(401, 'Grrrr!'));
        $expected = file_get_contents(__DIR__.'/Fixtures/401-json.txt');
        $this->assertSame(trim($expected), $formated);
    }

    public function testPropertiesGetter()
    {
        $formatter = new JsonFormatter($this->info);
        $this->assertFalse($formatter->isVerbose());
        $this->assertTrue($formatter->canFormat(new InvalidArgumentException()));
        $this->assertSame('application/problem+json', $formatter->contentType());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testThrowsOnInvalidEncoding()
    {
        $formatter = new JsonFormatter($this->info);
        $reflMethod = new \ReflectionMethod($formatter, 'toJson');
        $reflMethod->setAccessible(true);
        // send an invalid unicode sequence as a object that can't be cleaned
        $record = new \stdClass;
        $record->message = "\xB1\x31";
        $reflMethod->invoke($formatter, ['object' => $record]);
    }

    /**
     * @param int    $code
     * @param string $msg
     * @dataProvider providesHandleJsonErrorFailure
     */
    public function testHandleJsonErrorFailure($code, $msg)
    {
        $formatter = new JsonFormatter($this->info);
        $reflMethod = new \ReflectionMethod($formatter, 'throwEncodeError');
        $reflMethod->setAccessible(true);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage($msg);

        $reflMethod->invoke($formatter, $code, 'faked');
    }
    public function providesHandleJsonErrorFailure()
    {
        return [
            'depth' => [JSON_ERROR_DEPTH, 'Maximum stack depth exceeded'],
            'state' => [JSON_ERROR_STATE_MISMATCH, 'Underflow or the modes mismatch'],
            'ctrl' => [JSON_ERROR_CTRL_CHAR, 'Unexpected control character found'],
            'default' => [-1, 'Unknown error'],
        ];
    }


}
