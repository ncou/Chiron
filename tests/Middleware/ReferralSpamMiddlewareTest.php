<?php

declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Response;
use Chiron\Middleware\ReferralSpamMiddleware;
use Chiron\Tests\Utils\HandlerProxy2;
use PHPUnit\Framework\TestCase;

class ReferralSpamMiddlewareTest extends TestCase
{
    public function referrerSpamProvider()
    {
        return [
            [false, 'http://www.0n-line.tv'],
            [false, 'http://xn--90acenikpebbdd4f6d.xn--p1ai'], // it's the puny code for the IDN : 'http://холодныйобзвон.рф'
            [false, 'http://холодныйобзвон.рф'],
            [true, 'http://youtube.com'],
        ];
    }

    /**
     * @dataProvider referrerSpamProvider
     */
    public function testReferrerSpam(bool $allowed, string $refererHeader)
    {
        $request = (new ServerRequestFactory())->createServerRequestFromArray([
            'REQUEST_URI'            => '/',
            'REQUEST_METHOD'         => 'GET',
        ]);
        $request = $request->withHeader('Referer', $refererHeader);

        $middleware = new ReferralSpamMiddleware();
        $handler = function ($request) {
            return new Response();
        };

        $response = $middleware->process($request, new HandlerProxy2($handler));

        if ($allowed) {
            $this->assertEquals(200, $response->getStatusCode());
        } else {
            $this->assertEquals(403, $response->getStatusCode());
        }
    }
}
