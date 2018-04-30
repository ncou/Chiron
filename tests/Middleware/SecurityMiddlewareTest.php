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

class SecurityMiddlewareTest extends TestCase
{
    /**
     * @var string
     */
    private $configPath = __DIR__ . '/../config/secure-headers.php';

    public function test_disable_header()
    {
        $config = require $this->configPath;
        $config['x-download-options'] = null;
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertArrayNotHasKey('X-Download-Options', $headers);
    }

    public function test_hsts()
    {
        $config = require $this->configPath;
        $config['hsts']['enable'] = true;
        $config['hsts']['include-sub-domains'] = true;
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArraySubset([
            'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        ], $headers, true);
    }

    public function test_ect()
    {
        $config = require $this->configPath;
        $config['ect']['enable'] = true;
        $config['ect']['enforce'] = true;
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArraySubset([
            'Expect-CT' => 'max-age=63072000, enforce',
        ], $headers, true);
    }

    public function test_hpkp()
    {
        $config = require $this->configPath;
        $config['hpkp']['hashes'] = [
            '5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9',
            '6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',
        ];
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArrayHasKey('Public-Key-Pins', $headers);
    }

    public function test_custom_csp_empty()
    {
        $config = require $this->configPath;
        $config['custom-csp'] = '';
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
    }

    public function test_custom_csp()
    {
        $config = require $this->configPath;
        $config['custom-csp'] = 'foo';
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArraySubset([
            'Content-Security-Policy' => 'foo',
        ], $headers, true);
    }

    public function test_csp()
    {
        $config = require $this->configPath;
        $config['csp']['report-only'] = 'true';
        $headers = (new SecurityHeadersMiddleware($config))->getCompiledHeaders();
        $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $headers);
    }

    //@TODO : add some tests on the middleware, to check if the headers are presents !!!!
}
