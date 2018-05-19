<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org).
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * @see          http://cakephp.org CakePHP(tm) Project
 * @since         3.5.0
 *
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Chiron\Middleware;

// TODO : ajouter la gestion du cross origin CORS => https://zinoui.com/blog/cross-origin-resource-sharing

//https://securityheaders.io/

//https://github.com/BePsvPT/secure-headers/blob/master/src/Builder.php  +   https://github.com/BePsvPT/secure-headers/blob/master/src/SecureHeadersMiddleware.php
//https://github.com/cakephp/cakephp/blob/master/src/Http/Middleware/SecurityHeadersMiddleware.php
//https://github.com/paragonie/csp-builder/blob/master/src/CSPBuilder.php#L742

// TODO : à améliorer à partir de ce lien : https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#rp
// TODO : regarder ici : https://github.com/HumanDevice/yii2-tools/blob/master/components/SafeResponse.php

//https://scotthelme.co.uk/hpkp-http-public-key-pinning/

//https://www.keycdn.com/blog/http-security-headers/
//https://devcentral.f5.com/articles/tightening-the-security-of-http-traffic-part-2-27512
//https://blog.heroku.com/using-http-headers-to-secure-your-site
//https://blog.theodo.fr/2017/12/improve-website-security-5-minutes-http-headers/
//https://zinoui.com/blog/security-http-headers#expect-ct
//https://geekflare.com/http-header-implementation/
//https://www.contextis.com/blog/security-http-headers
//https://htaccessbook.com/increase-security-x-security-headers/
//https://docs.spring.io/spring-security/site/docs/current/reference/html/headers.html
//https://github.com/owncloud/core/issues/17613

//namespace Cake\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handles common security headers in a convenient way.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private $security = [];

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $securitySettings = [])
    {
        $this->security = $securitySettings;
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The request.
     * @param \Psr\Http\Message\ResponseInterface      $response The response.
     * @param callable                                 $next     Callback to invoke the next middleware.
     *
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cspHeaderManager = new \Chiron\CspHeaderManager($this->security['csp']);
        $request = $request->withAttribute('csp', $cspHeaderManager);

        $response = $handler->handle($request);

        $headers = $this->compileHeaders();
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    /**
     * Compile the security HTTP headers.
     *
     * @return array
     */
    public function compileHeaders(): array
    {
        return array_merge(
            $this->security['settings']['enable-hpkp'] ? $this->hpkp() : [],
            $this->security['settings']['enable-hsts'] ? $this->hsts() : [],
            $this->security['settings']['enable-ect'] ? $this->ect() : [],
            $this->security['settings']['enable-extras'] ? $this->extras() : []
        );
    }

    /**
     * Get HPKP header.
     *
     * @see https://developer.mozilla.org/fr/docs/Web/Security/Public_Key_Pinning
     *
     * @return array
     */
    private function hpkp(): array
    {
        // TODO : lever une exception si les champs hashes et max-age sont vide, car ils doivent tous les deux avoir une valeur chacun (cad un hash au minimum + un maxage)
        $values = [];
        foreach ($this->security['hpkp']['hashes'] as $hash) {
            $values[] = sprintf('pin-sha256="%s"', $hash);
        }
        $values[] = sprintf('max-age=%d', $this->security['hpkp']['max-age']);
        if ($this->security['hpkp']['include-sub-domains']) {
            $values[] = 'includeSubDomains';
        }
        if (! empty($this->security['hpkp']['report-uri'])) {
            $values[] = sprintf('report-uri="%s"', $this->security['hpkp']['report-uri']);
        }
        $header = $this->security['hpkp']['report-only']
            ? 'Public-Key-Pins-Report-Only'
            : 'Public-Key-Pins';

        return [$header => implode('; ', $values)];
    }

    /**
     * Get HSTS header.
     *
     * @return array
     */
    private function hsts(): array
    {
        $hsts = "max-age={$this->security['hsts']['max-age']}";

        if ($this->security['hsts']['include-sub-domains']) {
            $hsts .= '; includeSubDomains';
        }
        if ($this->security['hsts']['preload']) {
            $hsts .= '; preload';
        }

        return ['Strict-Transport-Security' => $hsts];
    }

    /**
     * Get Expected CT header.
     *
     * @return array
     */
    private function ect(): array
    {
        $ect = "max-age={$this->security['ect']['max-age']}";
        if ($this->security['ect']['enforce']) {
            $ect .= ', enforce';
        }

        if (! empty($this->security['ect']['report-uri'])) {
            $ect .= sprintf(', report-uri="%s"', $this->security['ect']['report-uri']);
        }

        return ['Expect-CT' => $ect];
    }

    /**
     * Get extras headers to add (array_filter will remove header if the value is : an empty string, a scalar 'false' or null).
     *
     * @return array
     */
    private function extras(): array
    {
        $headers = [];

        foreach ($this->security['extras'] as $header => $value) {
            $headers[ucwords($header, '-')] = $value;
        }

        return array_filter($headers);
    }
}
