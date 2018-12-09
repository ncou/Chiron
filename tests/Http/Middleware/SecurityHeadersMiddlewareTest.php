<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Middleware\SecurityHeadersMiddleware;
use Chiron\Http\Psr\Response;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Tests\Utils\RequestHandlerCallable;
use PHPUnit\Framework\TestCase;

class SecurityHeadersMiddlewareTest extends TestCase
{
    /**
     * @var string
     */
    private $configPath = __DIR__ . '/../../config/secure-headers.php';

    protected function setUp()
    {
        $this->request = new ServerRequest('GET', new Uri('/'));
    }

    public function test_disable_header()
    {
        $config = require $this->configPath;
        $config['x-download-options'] = null;

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertArrayNotHasKey('X-Download-Options', $headers);
    }

    public function test_hsts()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hsts'] = true;
        $config['hsts']['include-sub-domains'] = true;

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals(
            'max-age=63072000; includeSubDomains; preload',
            $response->getHeaderLine('Strict-Transport-Security')
        );
    }

    public function test_ect()
    {
        $config = require $this->configPath;
        $config['settings']['enable-ect'] = true;
        $config['ect']['enforce'] = true;

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals(
            'max-age=63072000, enforce',
            $response->getHeaderLine('Expect-CT')
        );
    }

    public function test_ect_report_only()
    {
        $config = require $this->configPath;
        $config['settings']['enable-ect'] = true;
        $config['ect']['enforce'] = true;
        $config['ect']['report-uri'] = 'www.example.com';

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals(
            'max-age=63072000, enforce, report-uri="www.example.com"',
            $response->getHeaderLine('Expect-CT')
        );
    }

    public function test_hpkp()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hpkp'] = true;
        $config['hpkp']['hashes'] = [
            '5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9',
            '6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',
        ];

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertArrayHasKey('Public-Key-Pins', $response->getHeaders());
        $this->assertEquals(
            'pin-sha256="5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9"; pin-sha256="6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b"; max-age=63072000; includeSubDomains',
            $response->getHeaderLine('Public-Key-Pins')
        );
    }

    public function test_hpkp_report_only()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hpkp'] = true;
        $config['hpkp']['hashes'] = ['foobar'];
        $config['hpkp']['report-only'] = true;
        $config['hpkp']['report-uri'] = 'www.example.com';

        $handler = function ($request) {
            return new Response();
        };
        $middleware = new SecurityHeadersMiddleware($config);
        $response = $middleware->process($this->request, new RequestHandlerCallable($handler));

        $this->assertEquals(
            'pin-sha256="foobar"; max-age=63072000; includeSubDomains; report-uri="www.example.com"',
            $response->getHeaderLine('Public-Key-Pins-Report-Only')
        );
    }

    /*
        public function test_csp_report_only()
        {
            $config = require $this->configPath;
            $config['settings']['enable-csp'] = true;
            $config['csp']['report-only'] = true;
            $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
            $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $headers);
        }

        //@TODO : add more tests for the CSP module
        public function test_csp()
        {
            $config = require $this->configPath;
            $config['settings']['enable-csp'] = true;
            $config['csp']['report-only'] = false;
            $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
            $this->assertArrayHasKey('Content-Security-Policy', $headers);
        }
    */

    //@TODO : add some tests on the middleware, to check if the headers are presents !!!!
}
