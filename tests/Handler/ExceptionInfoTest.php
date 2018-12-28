<?php

declare(strict_types=1);

namespace Tests\Handler;

use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Exception\HttpExceptionInterface;
use DOMDocument;
use DomXPath;
use PHPUnit\Framework\TestCase;
use Chiron\Handler\Formatter\JsonFormatter;
use Chiron\Handler\ExceptionInfo;
use Chiron\Http\Exception\HttpException;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Http\Exception\Client\PreconditionFailedHttpException;
use Chiron\Http\Exception\Client\UnprocessableEntityHttpException;
use Exception;
use InvalidArgumentException;


class ExceptionInfoTest extends TestCase
{
    private $info;

    protected function setUp()
    {
        $this->info = new ExceptionInfo(__DIR__.'/../../resources/lang/en/errors.json');
    }

    public function testExistingError()
    {
        $info = $this->info->generate(new BadRequestHttpException('Made a mess.'), 400);
        $expected = ['code' => 400, 'name' => 'Bad Request', 'detail' => 'Made a mess.'];
        $this->assertSame($expected, $info);
    }
    public function testShortError()
    {
        $info = $this->info->generate(new PreconditionFailedHttpException(':('), 412);
        $expected = ['code' => 412, 'name' => 'Precondition Failed', 'detail' => 'The server does not meet one of the preconditions that the requester put on the request.'];
        $this->assertSame($expected, $info);
    }
    public function testLongError()
    {
        $info = $this->info->generate(new UnprocessableEntityHttpException('Made a mess a really really big mess this time. Everything has broken, and unicorns are crying.'), 422);
        $expected = ['code' => 422, 'name' => 'Unprocessable Entity', 'detail' => 'Made a mess a really really big mess this time. Everything has broken, and unicorns are crying.'];
        $this->assertSame($expected, $info);
    }
    public function testBadError()
    {
        $info = $this->info->generate(new Exception('Ooops.'),  666);
        $expected = [ 'code' => 500, 'name' => 'Internal Server Error', 'detail' => 'An error has occurred and this resource cannot be displayed.'];
        $this->assertSame($expected, $info);
    }
    public function testHiddenError()
    {
        $info = $this->info->generate(new InvalidArgumentException('Made another mess.'), 503);
        $expected = ['code' => 503, 'name' => 'Service Unavailable', 'detail' => 'The server is currently unavailable. It may be overloaded or down for maintenance.'];
        $this->assertSame($expected, $info);
    }
    public function testFallbackWhenNoPathForErrorFileIsUsed()
    {
        $info = (new ExceptionInfo())->generate(new BadRequestHttpException(), 400);
        $expected = ['code' => 500, 'name' => 'Internal Server Error', 'detail' => 'An error has occurred and this resource cannot be displayed.'];
        $this->assertSame($expected, $info);
    }
}
