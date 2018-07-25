<?php

declare(strict_types=1);

namespace Tests\Handler\Error;

use Chiron\Http\Exception\HttpException;
use Chiron\Handler\Error\ExceptionHelper;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\Uri;
use Chiron\Http\StatusCode;
use DOMDocument;
use DomXPath;
use PHPUnit\Framework\TestCase;

class ExceptionHelperTest extends TestCase
{
    public const URL_IANA = 'https://www.iana.org/assignments/http-status-codes/http-status-codes.xml';

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Unknown http status code: `509`.
     */
    public function testgetExceptionNameByStatusCodeToThrowOutOfBoundsException(): void
    {
        ExceptionHelper::getExceptionNameByStatusCode(509);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid status code '200'; must be an integer between 400 and 599, inclusive.
     */
    public function testgetExceptionNameByStatusCodeToThrowInvalidArgumentException(): void
    {
        ExceptionHelper::getExceptionNameByStatusCode(200);
    }

    /**
     * @dataProvider ianaCodesReasonPhrasesProvider
     *
     * @param mixed $code
     * @param mixed $reasonPhrase
     */
    public function testGetReasonExceptionAgainstIana($code, $reasonPhrase): void
    {
        // skip http code from 100 to 399 because client and server errors are in the range 4xx-5xx
        if ($code < 400) {
            self::assertTrue(true);
        } else {
            $exceptionName = ExceptionHelper::getExceptionNameByStatusCode((int) $code);
            // the HTTPException for 401/407/426 class requiere a mandatory parameter
            if (strpos($exceptionName, 'UnauthorizedHttpException') > 0 || strpos($exceptionName, 'ProxyAuthenticationRequiredHttpException') > 0 || strpos($exceptionName, 'UpgradeRequiredHttpException') > 0) {
                $exception = new $exceptionName('foo');
            } else {
                $exception = new $exceptionName();
            }

            $this->assertInstanceOf(HttpException::class, $exception);
            self::assertSame($reasonPhrase, $exception->getMessage());
            self::assertSame((int) $code, $exception->getStatusCode());
        }
    }

    /**
     * Grab the reason phrase directly from the IANA specs.
     */
    public function ianaCodesReasonPhrasesProvider(): array
    {
        if (! in_array('https', stream_get_wrappers(), true)) {
            $this->markTestSkipped('The "https" wrapper is not available');
        }
        $ianaHttpStatusCodes = new DOMDocument();
        libxml_set_streams_context(stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 30,
            ],
        ]));
        $ianaHttpStatusCodes->load(self::URL_IANA);
        if (! $ianaHttpStatusCodes->relaxNGValidate(__DIR__ . '/../../schema/http-status-codes.rng')) {
            self::fail('Invalid IANA\'s HTTP status code list.');
        }
        $ianaCodesReasonPhrases = [];
        $xpath = new DomXPath($ianaHttpStatusCodes);
        $xpath->registerNamespace('ns', 'http://www.iana.org/assignments');
        $records = $xpath->query('//ns:record');
        foreach ($records as $record) {
            $value = $xpath->query('.//ns:value', $record)->item(0)->nodeValue;
            $description = $xpath->query('.//ns:description', $record)->item(0)->nodeValue;
            if (in_array($description, ['Unassigned', '(Unused)'], true)) {
                continue;
            }
            if (preg_match('/^([0-9]+)\s*\-\s*([0-9]+)$/', $value, $matches)) {
                for ($value = $matches[1]; $value <= $matches[2]; $value++) {
                    $ianaCodesReasonPhrases[] = [$value, $description];
                }
            } else {
                $ianaCodesReasonPhrases[] = [$value, $description];
            }
        }

        return $ianaCodesReasonPhrases;
    }
}
