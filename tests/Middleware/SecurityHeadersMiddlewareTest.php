<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Chiron\Tests\Middleware;

use Chiron\Middleware\SecurityHeadersMiddleware;
use PHPUnit\Framework\TestCase;

class SecurityHeadersMiddlewareTest extends TestCase
{
    /**
     * @var string
     */
    private $configPath = __DIR__ . '/../config/secure-headers.php';

    public function test_disable_header()
    {
        $config = require $this->configPath;
        $config['x-download-options'] = null;
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertArrayNotHasKey('X-Download-Options', $headers);
    }

    public function test_hsts()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hsts'] = true;
        $config['hsts']['include-sub-domains'] = true;
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArraySubset([
            'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        ], $headers, true);
    }

    public function test_ect()
    {
        $config = require $this->configPath;
        $config['settings']['enable-ect'] = true;
        $config['ect']['enforce'] = true;
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArraySubset([
            'Expect-CT' => 'max-age=63072000, enforce',
        ], $headers, true);
    }

    public function test_ect_report_only()
    {
        $config = require $this->configPath;
        $config['settings']['enable-ect'] = true;
        $config['ect']['enforce'] = true;
        $config['ect']['report-uri'] = 'www.example.com';
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArraySubset([
            'Expect-CT' => 'max-age=63072000, enforce, report-uri="www.example.com"',
        ], $headers, true);
    }

    public function test_hpkp()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hpkp'] = true;
        $config['hpkp']['hashes'] = [
            '5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9',
            '6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',
        ];
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArrayHasKey('Public-Key-Pins', $headers);
    }

    public function test_hpkp_report_only()
    {
        $config = require $this->configPath;
        $config['settings']['enable-hpkp'] = true;
        $config['hpkp']['hashes'] = ['foobar'];
        $config['hpkp']['report-only'] = true;
        $config['hpkp']['report-uri'] = 'www.example.com';
        $headers = (new SecurityHeadersMiddleware($config))->compileHeaders();
        $this->assertArraySubset([
            'Public-Key-Pins-Report-Only' => 'pin-sha256="foobar"; max-age=63072000; includeSubDomains; report-uri="www.example.com"',
        ], $headers, true);
    }

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

    //@TODO : add some tests on the middleware, to check if the headers are presents !!!!
}
