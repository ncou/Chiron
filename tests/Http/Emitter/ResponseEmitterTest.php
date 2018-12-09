<?php

declare(strict_types=1);

// TODO : utiliser cette classe pour ajouter des tests : https://github.com/zendframework/zend-diactoros/blob/master/test/Response/SapiStreamEmitterTest.php
// https://github.com/cakephp/cakephp/blob/master/tests/TestCase/Http/ResponseEmitterTest.php

namespace Chiron\Tests\Http\Emitter;

use Chiron\Http\Psr\Response;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Tests\Utils\CallbackStream;
use Chiron\Tests\Utils\HeaderStack;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ResponseEmitterTest extends TestCase
{
    private $emitter;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->emitter = new ResponseEmitter();
        HeaderStack::reset();
    }

    protected function tearDown()
    {
        HeaderStack::reset();
    }

    public function testEmitsResponseHeaders(): void
    {
        $response = (new Response())
                ->withStatus(200)
                ->withAddedHeader('Content-Type', 'text/plain');

        ob_start();
        $this->emitter->emit($response);
        ob_end_clean();

        self::assertTrue(HeaderStack::has('HTTP/1.1 200 OK'));
        self::assertTrue(HeaderStack::has('Content-Type: text/plain'));
    }

    public function testEmitsMessageBody(): void
    {
        $response = (new Response())
                ->withStatus(200)
                ->withAddedHeader('Content-Type', 'text/plain');
        $response->getBody()->write('Content!');

        $this->expectOutputString('Content!');

        $this->emitter->emit($response);
    }

    public function testEmitCallbackStreamResponseUnseekableBody()
    {
        $response = (new Response())
                ->withStatus(200);

        $body = new CallbackStream(function () {
            return 'Content!';
        });
        $response = $response->withBody($body);

        $this->expectOutputString('Content!');

        $this->emitter->emit($response);
    }

    public function testMultipleSetCookieHeadersAreNotReplaced(): void
    {
        $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Set-Cookie', 'foo=bar')
            ->withAddedHeader('Set-Cookie', 'bar=baz');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'Set-Cookie: foo=bar', 'replace' => false, 'status_code' => 200],
            ['header' => 'Set-Cookie: bar=baz', 'replace' => false, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    public function testDoesNotLetResponseCodeBeOverriddenByPHP(): void
    {
        $response = (new Response())
            ->withStatus(202)
            ->withAddedHeader('Location', 'http://api.my-service.com/12345678')
            ->withAddedHeader('Content-Type', 'text/plain');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'Location: http://api.my-service.com/12345678', 'replace' => true, 'status_code' => 202],
            ['header' => 'Content-Type: text/plain', 'replace' => true, 'status_code' => 202],
            ['header' => 'HTTP/1.1 202 Accepted', 'replace' => true, 'status_code' => 202],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    public function testEmitterRespectLocationHeader(): void
    {
        $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Location', 'http://api.my-service.com/12345678');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'Location: http://api.my-service.com/12345678', 'replace' => true, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    /**
     * Test emitting a no-content response.
     */
    // TODO : faire aussi ce test avec un emitBodyRange !!!!! et pas seulement avec la méthode emitBody !!!!!
    public function testEmitNoContentResponse()
    {
        $response = (new Response())
            ->withHeader('X-testing', 'value')
            ->withStatus(204);
        $response->getBody()->write('It worked');

        $this->expectOutputString('');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'X-testing: value', 'replace' => true, 'status_code' => 204],
            ['header' => 'HTTP/1.1 204 No Content', 'replace' => true, 'status_code' => 204],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    // ***********************************************
    // *************** EMIT BODY RANGE ***************
    // ***********************************************

    /**
     * Test valid body ranges.
     */
    public function testEmitResponseBodyRange()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 1-4/9');
        $response->getBody()->write('It worked');

        $this->expectOutputString('t wo');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'Content-Type: text/plain', 'replace' => true, 'status_code' => 200],
            ['header' => 'Content-Range: bytes 1-4/9', 'replace' => true, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    /**
     * Test valid body ranges.
     */
    public function testEmitResponseBodyRangeComplete()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 0-20/9');
        $response->getBody()->write('It worked');

        $this->expectOutputString('It worked');

        $this->emitter->setMaxBufferLength(2);
        $this->emitter->emit($response);
    }

    /**
     * Test out of bounds body ranges.
     */
    public function testEmitResponseBodyRangeOverflow()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 5-20/*');
        $response->getBody()->write('It worked');

        $this->expectOutputString('rked');

        $this->emitter->emit($response);
    }

    /**
     * Test malformed content-range header.
     */
    public function testEmitResponseBodyRangeMalformed()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 9-ba/a');
        $response->getBody()->write('It worked');

        $this->expectOutputString('It worked');

        $this->emitter->emit($response);
    }

    /**
     * Test malformed content-range header.
     */
    public function testEmitResponseBodyRangeWithBadUnit()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'foobar 5-20/9');
        $response->getBody()->write('It worked');

        $this->expectOutputString('It worked');

        $this->emitter->emit($response);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEmitResponseBodyRangeMalTooBig()
    {
        // TODO : on devrait peut être ignorer un range qui est trop important par rapport à la tailler du fichier ??? non ??? et donc modifier ce test !!!!

        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 50-100/9');
        $response->getBody()->write('It worked');

        //$this->expectOutputString('It worked');

        $this->emitter->emit($response);
    }

    public function testContentRangeUnseekableBody()
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Range', 'bytes 5-20/*');

        $body = new CallbackStream(function () {
            return 'It worked';
        });

        $response = $response->withBody($body);

        $this->expectOutputString('rked');

        $this->emitter->emit($response);
    }

    // *************************************************************
    // *************** ADVANCED TESTS FOR BODY RANGE ***************
    // *************************************************************

    /**
     * Create a new stream prophecy and setup common promises.
     *
     * @param string|callable $contents              Stream contents.
     * @param int             $size                  Size of stream contents.
     * @param int             $startPosition         Start position of internal stream data pointer.
     * @param callable|null   $trackPeakBufferLength Called on "read" calls.
     *                                               Receives data length (i.e. data length <= buffer length).
     *
     * @return ObjectProphecy Returns new stream prophecy.
     */
    private function setUpStreamProphecy($contents, $size, $startPosition, callable $trackPeakBufferLength = null)
    {
        $position = $startPosition;
        $stream = $this->prophesize('Psr\Http\Message\StreamInterface');
        $stream->__toString()->will(function () use ($contents, $size, &$position) {
            if (is_callable($contents)) {
                $data = $contents(0);
            } else {
                $data = $contents;
            }
            $position = $size;

            return $data;
        });
        $stream->getSize()->willReturn($size);
        $stream->tell()->will(function () use (&$position) {
            return $position;
        });
        $stream->eof()->will(function () use ($size, &$position) {
            return $position >= $size;
        });
        $stream->seek(Argument::type('integer'), Argument::any())->will(function ($args) use ($size, &$position) {
            if ($args[0] < $size) {
                $position = $args[0];

                return true;
            }

            return false;
        });
        $stream->rewind()->will(function () use (&$position) {
            $position = 0;

            return true;
        });
        $stream->read(Argument::type('integer'))
            ->will(function ($args) use ($contents, &$position, &$trackPeakBufferLength) {
                if (is_callable($contents)) {
                    $data = $contents($position, $args[0]);
                } else {
                    $data = substr($contents, $position, $args[0]);
                }
                if ($trackPeakBufferLength) {
                    $trackPeakBufferLength($args[0]);
                }
                $position += strlen($data);

                return $data;
            });
        $stream->getContents()->will(function () use ($contents, &$position) {
            if (is_callable($contents)) {
                $remainingContents = $contents($position);
            } else {
                $remainingContents = substr($contents, $position);
            }
            $position += strlen($remainingContents);

            return $remainingContents;
        });

        return $stream;
    }

    public function emitStreamResponseProvider()
    {
        return [
            [true,   true,    '01234567890987654321',   10],
            [true,   true,    '01234567890987654321',   20],
            [true,   true,    '01234567890987654321',  100],
            [true,   true, '01234567890987654321012',   10],
            [true,   true, '01234567890987654321012',   20],
            [true,   true, '01234567890987654321012',  100],
            [true,  false,    '01234567890987654321',   10],
            [true,  false,    '01234567890987654321',   20],
            [true,  false,    '01234567890987654321',  100],
            [true,  false, '01234567890987654321012',   10],
            [true,  false, '01234567890987654321012',   20],
            [true,  false, '01234567890987654321012',  100],
            [false,  true,    '01234567890987654321',   10],
            [false,  true,    '01234567890987654321',   20],
            [false,  true,    '01234567890987654321',  100],
            [false,  true, '01234567890987654321012',   10],
            [false,  true, '01234567890987654321012',   20],
            [false,  true, '01234567890987654321012',  100],
            [false, false,    '01234567890987654321',   10],
            [false, false,    '01234567890987654321',   20],
            [false, false,    '01234567890987654321',  100],
            [false, false, '01234567890987654321012',   10],
            [false, false, '01234567890987654321012',   20],
            [false, false, '01234567890987654321012',  100],
        ];
    }

    /**
     * @param bool   $seekable        Indicates if stream is seekable
     * @param bool   $readable        Indicates if stream is readable
     * @param string $contents        Contents stored in stream
     * @param int    $maxBufferLength Maximum buffer length used in the emitter call.
     * @dataProvider emitStreamResponseProvider
     */
    public function testEmitStreamResponse($seekable, $readable, $contents, $maxBufferLength)
    {
        $size = strlen($contents);
        $startPosition = 0;
        $peakBufferLength = 0;
        $rewindCalled = false;
        $fullContentsCalled = false;
        $stream = $this->setUpStreamProphecy(
            $contents,
            $size,
            $startPosition,
            function ($bufferLength) use (&$peakBufferLength) {
                if ($bufferLength > $peakBufferLength) {
                    $peakBufferLength = $bufferLength;
                }
            }
        );
        $stream->isSeekable()->willReturn($seekable);
        $stream->isReadable()->willReturn($readable);

        $response = (new Response())
            ->withStatus(200)
            ->withBody($stream->reveal());

        ob_start();
        $this->emitter->setMaxBufferLength($maxBufferLength);
        $this->emitter->emit($response);
        $emittedContents = ob_get_clean();

        if ($seekable) {
            $rewindPredictionClosure = function () use (&$rewindCalled) {
                $rewindCalled = true;
            };
            $stream->rewind()->should($rewindPredictionClosure);
            $stream->seek(0)->should($rewindPredictionClosure);
            $stream->seek(0, SEEK_SET)->should($rewindPredictionClosure);
        } else {
            $stream->rewind()->shouldNotBeCalled();
            $stream->seek(Argument::type('integer'), Argument::any())->shouldNotBeCalled();
        }
        if ($readable) {
            $stream->__toString()->shouldNotBeCalled();
            $stream->read(Argument::type('integer'))->shouldBeCalled();
            $stream->eof()->shouldBeCalled();
            $stream->getContents()->shouldNotBeCalled();
        } else {
            $fullContentsPredictionClosure = function () use (&$fullContentsCalled) {
                $fullContentsCalled = true;
            };
            $stream->__toString()->should($fullContentsPredictionClosure);
            $stream->read(Argument::type('integer'))->shouldNotBeCalled();
            $stream->eof()->shouldNotBeCalled();
            if ($seekable) {
                $stream->getContents()->should($fullContentsPredictionClosure);
            } else {
                $stream->getContents()->shouldNotBeCalled();
            }
        }
        $stream->checkProphecyMethodsPredictions();
        $this->assertSame($seekable, $rewindCalled);
        $this->assertSame(! $readable, $fullContentsCalled);
        $this->assertSame($contents, $emittedContents);
        $this->assertLessThanOrEqual($maxBufferLength, $peakBufferLength);
    }

    public function emitRangeStreamResponseProvider()
    {
        return [
            [true,   true, ['bytes', 10,  20, '*'],    '01234567890987654321',   5],
            [true,   true, ['bytes', 10,  20, '*'],    '01234567890987654321',  10],
            [true,   true, ['bytes', 10,  20, '*'],    '01234567890987654321', 100],
            [true,   true, ['bytes', 10,  20, '*'], '01234567890987654321012',   5],
            [true,   true, ['bytes', 10,  20, '*'], '01234567890987654321012',  10],
            [true,   true, ['bytes', 10,  20, '*'], '01234567890987654321012', 100],
            [true,   true, ['bytes', 10, 100, '*'],    '01234567890987654321',   5],
            [true,   true, ['bytes', 10, 100, '*'],    '01234567890987654321',  10],
            [true,   true, ['bytes', 10, 100, '*'],    '01234567890987654321', 100],
            [true,   true, ['bytes', 10, 100, '*'], '01234567890987654321012',   5],
            [true,   true, ['bytes', 10, 100, '*'], '01234567890987654321012',  10],
            [true,   true, ['bytes', 10, 100, '*'], '01234567890987654321012', 100],
            [true,  false, ['bytes', 10,  20, '*'],    '01234567890987654321',   5],
            [true,  false, ['bytes', 10,  20, '*'],    '01234567890987654321',  10],
            [true,  false, ['bytes', 10,  20, '*'],    '01234567890987654321', 100],
            [true,  false, ['bytes', 10,  20, '*'], '01234567890987654321012',   5],
            [true,  false, ['bytes', 10,  20, '*'], '01234567890987654321012',  10],
            [true,  false, ['bytes', 10,  20, '*'], '01234567890987654321012', 100],
            [true,  false, ['bytes', 10, 100, '*'],    '01234567890987654321',   5],
            [true,  false, ['bytes', 10, 100, '*'],    '01234567890987654321',  10],
            [true,  false, ['bytes', 10, 100, '*'],    '01234567890987654321', 100],
            [true,  false, ['bytes', 10, 100, '*'], '01234567890987654321012',   5],
            [true,  false, ['bytes', 10, 100, '*'], '01234567890987654321012',  10],
            [true,  false, ['bytes', 10, 100, '*'], '01234567890987654321012', 100],
            [false,  true, ['bytes', 10,  20, '*'],    '01234567890987654321',   5],
            [false,  true, ['bytes', 10,  20, '*'],    '01234567890987654321',  10],
            [false,  true, ['bytes', 10,  20, '*'],    '01234567890987654321', 100],
            [false,  true, ['bytes', 10,  20, '*'], '01234567890987654321012',   5],
            [false,  true, ['bytes', 10,  20, '*'], '01234567890987654321012',  10],
            [false,  true, ['bytes', 10,  20, '*'], '01234567890987654321012', 100],
            [false,  true, ['bytes', 10, 100, '*'],    '01234567890987654321',   5],
            [false,  true, ['bytes', 10, 100, '*'],    '01234567890987654321',  10],
            [false,  true, ['bytes', 10, 100, '*'],    '01234567890987654321', 100],
            [false,  true, ['bytes', 10, 100, '*'], '01234567890987654321012',   5],
            [false,  true, ['bytes', 10, 100, '*'], '01234567890987654321012',  10],
            [false,  true, ['bytes', 10, 100, '*'], '01234567890987654321012', 100],
            [false, false, ['bytes', 10,  20, '*'],    '01234567890987654321',   5],
            [false, false, ['bytes', 10,  20, '*'],    '01234567890987654321',  10],
            [false, false, ['bytes', 10,  20, '*'],    '01234567890987654321', 100],
            [false, false, ['bytes', 10,  20, '*'], '01234567890987654321012',   5],
            [false, false, ['bytes', 10,  20, '*'], '01234567890987654321012',  10],
            [false, false, ['bytes', 10,  20, '*'], '01234567890987654321012', 100],
            [false, false, ['bytes', 10, 100, '*'],    '01234567890987654321',   5],
            [false, false, ['bytes', 10, 100, '*'],    '01234567890987654321',  10],
            [false, false, ['bytes', 10, 100, '*'],    '01234567890987654321', 100],
            [false, false, ['bytes', 10, 100, '*'], '01234567890987654321012',   5],
            [false, false, ['bytes', 10, 100, '*'], '01234567890987654321012',  10],
            [false, false, ['bytes', 10, 100, '*'], '01234567890987654321012', 100],
        ];
    }

    /**
     * @param bool   $seekable        Indicates if stream is seekable
     * @param bool   $readable        Indicates if stream is readable
     * @param array  $range           Emitted range of data [$unit, $first, $last, $length]
     * @param string $contents        Contents stored in stream
     * @param int    $maxBufferLength Maximum buffer length used in the emitter call.
     * @dataProvider emitRangeStreamResponseProvider
     */
    public function testEmitRangeStreamResponse($seekable, $readable, array $range, $contents, $maxBufferLength)
    {
        list($unit, $first, $last, $length) = $range;
        $size = strlen($contents);
        if ($readable && ! $seekable) {
            $startPosition = $first;
        } else {
            $startPosition = 0;
        }
        $peakBufferLength = 0;
        $seekCalled = false;
        $stream = $this->setUpStreamProphecy(
            $contents,
            $size,
            $startPosition,
            function ($bufferLength) use (&$peakBufferLength) {
                if ($bufferLength > $peakBufferLength) {
                    $peakBufferLength = $bufferLength;
                }
            }
        );
        $stream->isSeekable()->willReturn($seekable);
        $stream->isReadable()->willReturn($readable);

        $response = (new Response())
            ->withStatus(200)
            ->withHeader('Content-Range', 'bytes ' . $first . '-' . $last . '/*')
            ->withBody($stream->reveal());

        ob_start();
        $this->emitter->setMaxBufferLength($maxBufferLength);
        $this->emitter->emit($response);
        $emittedContents = ob_get_clean();

        $stream->rewind()->shouldNotBeCalled();
        if ($seekable) {
            $seekPredictionClosure = function () use (&$seekCalled) {
                $seekCalled = true;
            };
            $stream->seek($first)->should($seekPredictionClosure);
            $stream->seek($first, SEEK_SET)->should($seekPredictionClosure);
        } else {
            $stream->seek(Argument::type('integer'), Argument::any())->shouldNotBeCalled();
        }
        $stream->__toString()->shouldNotBeCalled();
        if ($readable) {
            $stream->read(Argument::type('integer'))->shouldBeCalled();
            $stream->eof()->shouldBeCalled();
            $stream->getContents()->shouldNotBeCalled();
        } else {
            $stream->read(Argument::type('integer'))->shouldNotBeCalled();
            $stream->eof()->shouldNotBeCalled();
            $stream->getContents()->shouldBeCalled();
        }
        $stream->checkProphecyMethodsPredictions();
        $this->assertSame($seekable, $seekCalled);
        $this->assertSame(substr($contents, $first, $last - $first + 1), $emittedContents);
        $this->assertLessThanOrEqual($maxBufferLength, $peakBufferLength);
    }

    public function emitMemoryUsageProvider()
    {
        return [
            [true,   true,  1000,   20,       null,  512],
            [true,   true,  1000,   20,       null, 4096],
            [true,   true,  1000,   20,       null, 8192],
            [true,  false,   100,  320,       null,  512],
            [true,  false,   100,  320,       null, 4096],
            [true,  false,   100,  320,       null, 8192],
            [false,  true,  1000,   20,       null,  512],
            [false,  true,  1000,   20,       null, 4096],
            [false,  true,  1000,   20,       null, 8192],
            [false, false,   100,  320,       null,  512],
            [false, false,   100,  320,       null, 4096],
            [false, false,   100,  320,       null, 8192],
            [true,   true,  1000,   20,   [25, 75],  512],
            [true,   true,  1000,   20,   [25, 75], 4096],
            [true,   true,  1000,   20,   [25, 75], 8192],
            [false,  true,  1000,   20,   [25, 75],  512],
            [false,  true,  1000,   20,   [25, 75], 4096],
            [false,  true,  1000,   20,   [25, 75], 8192],
            [true,   true,  1000,   20, [250, 750],  512],
            [true,   true,  1000,   20, [250, 750], 4096],
            [true,   true,  1000,   20, [250, 750], 8192],
            [false,  true,  1000,   20, [250, 750],  512],
            [false,  true,  1000,   20, [250, 750], 4096],
            [false,  true,  1000,   20, [250, 750], 8192],
        ];
    }

    /**
     * @param bool       $seekable         Indicates if stream is seekable
     * @param bool       $readable         Indicates if stream is readable
     * @param int        $sizeBlocks       Number the blocks of stream data.
     *                                     Block size is equal to $maxBufferLength.
     * @param int        $maxAllowedBlocks Maximum allowed memory usage in block units.
     * @param array|null $rangeBlocks      Emitted range of data in block units [$firstBlock, $lastBlock].
     * @param int        $maxBufferLength  Maximum buffer length used in the emitter call.
     *
     * @dataProvider emitMemoryUsageProvider
     */
    public function testEmitMemoryUsage(
        $seekable,
        $readable,
        $sizeBlocks,
        $maxAllowedBlocks,
        $rangeBlocks,
        $maxBufferLength
    ) {
        $sizeBytes = $maxBufferLength * $sizeBlocks;
        $maxAllowedMemoryUsage = $maxBufferLength * $maxAllowedBlocks;
        $peakBufferLength = 0;
        $peakMemoryUsage = 0;
        $position = 0;

        $first = null;
        $last = null;

        if ($rangeBlocks) {
            $first = $maxBufferLength * $rangeBlocks[0];
            $last = ($maxBufferLength * $rangeBlocks[1]) + $maxBufferLength - 1;
            if ($readable && ! $seekable) {
                $position = $first;
            }
        }
        $closureTrackMemoryUsage = function () use (&$peakMemoryUsage) {
            $peakMemoryUsage = max($peakMemoryUsage, memory_get_usage());
        };
        $stream = $this->setUpStreamProphecy(
            function ($position, $length = null) use (&$sizeBytes) {
                if (! $length) {
                    $length = $sizeBytes - $position;
                }

                return str_repeat('0', $length);
            },
            $sizeBytes,
            $position,
            function ($bufferLength) use (&$peakBufferLength) {
                if ($bufferLength > $peakBufferLength) {
                    $peakBufferLength = $bufferLength;
                }
            }
        );
        $stream->isSeekable()->willReturn($seekable);
        $stream->isReadable()->willReturn($readable);

        $response = (new Response())
            ->withStatus(200)
            ->withBody($stream->reveal());

        if ($rangeBlocks) {
            $response = $response->withHeader('Content-Range', 'bytes ' . $first . '-' . $last . '/*');
        }

        ob_start(
            function () use (&$closureTrackMemoryUsage) {
                $closureTrackMemoryUsage();

                return '';
            },
            $maxBufferLength
        );
        gc_collect_cycles();
        gc_disable();
        $this->emitter->setMaxBufferLength($maxBufferLength);
        $this->emitter->emit($response);
        ob_end_flush();
        gc_enable();
        gc_collect_cycles();
        $localMemoryUsage = memory_get_usage();
        $this->assertLessThanOrEqual($maxBufferLength, $peakBufferLength);
        $this->assertLessThanOrEqual($maxAllowedMemoryUsage, $peakMemoryUsage - $localMemoryUsage);
    }

    public function testResponseReplacesPreviouslySetHeaders()
    {
        $response = (new Response())
            ->withHeader('X-Foo', 'baz1')
            ->withAddedHeader('X-Foo', 'baz2');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'X-Foo: baz1', 'replace' => true, 'status_code' => 200],
            ['header' => 'X-Foo: baz2', 'replace' => false, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }

    public function testResponseDoesNotReplacePreviouslySetSetCookieHeaders()
    {
        $response = (new Response())
            ->withHeader('Set-Cookie', 'foo=bar')
            ->withAddedHeader('Set-Cookie', 'bar=baz');

        $this->emitter->emit($response);

        $expectedStack = [
            ['header' => 'Set-Cookie: foo=bar', 'replace' => false, 'status_code' => 200],
            ['header' => 'Set-Cookie: bar=baz', 'replace' => false, 'status_code' => 200],
            ['header' => 'HTTP/1.1 200 OK', 'replace' => true, 'status_code' => 200],
        ];
        self::assertSame($expectedStack, HeaderStack::stack());
    }
}
