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
    private const CSP_DIRECTIVES = [
        // child-src has been removed because it's deprecated !
        'default-src',
        'base-uri',
        'connect-src',
        'font-src',
        'form-action',
        'frame-ancestors',
        'frame-src',
        'img-src',
        'manifest-src',
        'media-src',
        'object-src',
        'plugin-types',
        'sandbox',
        'script-src',
        'style-src',
        'worker-src',
    ];

    private $config = [];

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get CSP header.
     *
     * @return array
     */
    private function csp()
    {
        $values = [];
        foreach (self::CSP_DIRECTIVES as $directive) {
            if (isset($this->config['csp'][$directive])) {
                $values[] = $this->compileCspDirective($directive, $this->config['csp'][$directive]);
            }
        }
        if (! empty($this->config['csp']['block-all-mixed-content'])) {
            $values[] = 'block-all-mixed-content';
        }
        if (! empty($this->config['csp']['upgrade-insecure-requests'])) {
            $values[] = 'upgrade-insecure-requests';
        }
        if (! empty($this->config['csp']['require-sri-for'])) {
            $values[] = sprintf('require-sri-for %s', $this->config['csp']['require-sri-for']);
        }
        if (! empty($this->config['csp']['report-uri'])) {
            $values[] = sprintf('report-uri %s', $this->config['csp']['report-uri']);
            // support new navigator wich use now "report-to" instead of "report-uri"
            $values[] = sprintf('report-to %s', $this->config['csp']['report-uri']);
        }
        // TODO : lever une exception si on utilise report-only mais que le report-uri n'est pas défini car on va consommer du CPU pour rien au final !!!!!!
        $header = ! empty($this->config['csp']['report-only'])
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        return [$header => implode('; ', array_filter($values, 'strlen'))];
    }

    /**
     * Compile a subgroup into a policy string.
     *
     * @param string $directive
     * @param mixed  $policies
     *
     * @return string
     */
    private function compileCspDirective(string $directive, $policies): string
    {
        // handle special directive first
        switch ($directive) {
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-types
            case 'plugin-types':
                return empty($policies) ? '' : sprintf('%s %s', $directive, implode(' ', $policies));
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/require-sri-for
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox
//            case 'require-sri-for':
//                return empty($policies) ? '' : sprintf('%s %s', $directive, $policies);
            case 'sandbox':
            // TODO : améliorer la gestion de sandbox car on peut avoir une chaine vide, et donc afficher uniquement 'sandbox;'. il faudrait plutot gérer si c'est null on n'affiche rien, si c'est vide on affiche que sandbox
                return empty($policies) ? '' : trim(sprintf('%s %s', $directive, trim(implode(' ', $policies))));
        }
        // when policies is empty, we assume that user disallow this directive
        if (empty($policies)) {
            return sprintf("%s 'none'", $directive);
        }
        $ret = [$directive];
        // keyword source, https://www.w3.org/TR/CSP/#grammardef-keyword-source, https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
        foreach (['self', 'unsafe-inline', 'unsafe-eval', 'strict-dynamic', 'unsafe-hashed-attributes', 'report-sample'] as $keyword) {
            if (! empty($policies[$keyword])) {
                $ret[] = sprintf("'%s'", $keyword);
            }
        }
        if (! empty($policies['allow'])) {
            foreach ($policies['allow'] as $url) {
                $url = filter_var($url, FILTER_SANITIZE_URL);
                if ($url !== false) {
                    $ret[] = $url;
                }
            }
        }
        if (! empty($policies['hashes'])) {
            foreach ($policies['hashes'] as $algo => $hashes) {
                // skip not support algorithm, https://www.w3.org/TR/CSP/#grammardef-hash-source
                // TODO : il faudrait plutot lever une exception !!!!
                if (! in_array($algo, ['sha256', 'sha384', 'sha512'])) {
                    continue;
                }
                foreach ($hashes as $hashval) {
                    // skip invalid value
                    $ret[] = sprintf("'%s-%s'", $algo, preg_replace('~[^A-Za-z0-9+/=]~', '', $hashval));
                }
            }
        }
        if (! empty($policies['nonces'])) {
            foreach ($policies['nonces'] as $nonce) {
                // skip invalid value, https://www.w3.org/TR/CSP/#grammardef-nonce-source
                $ret[] = sprintf("'nonce-%s'", preg_replace('~[^A-Za-z0-9+/=]~', '', $nonce));
            }
        }
        if (! empty($policies['schemes'])) {
            // TODO : lever une exception si la valeur n'est pas présente dans cette liste de mots clés => 'http:', 'https:', 'blob:', 'data:', 'mediastream:', 'filesystem:'
            // TODO : forcer le caractére "deux points" à la fin du mot clés au cas ou l'utilisateur l'aurai oublié :(
            foreach ($policies['schemes'] as $scheme) {
                $ret[] = sprintf('%s', $scheme);
            }
        }

        return implode(' ', $ret);
    }

    /**
     * Get HPKP header.
     *
     * @see https://developer.mozilla.org/fr/docs/Web/Security/Public_Key_Pinning
     *
     * @return array
     */
    private function hpkp()
    {
        // TODO : lever une exception si les champs hashes et max-age sont vide, car ils doivent tous les deux avoir une valeur chacun (cad un hash au minimum + un maxage)
        $values = [];
        foreach ($this->config['hpkp']['hashes'] as $hash) {
            $values[] = sprintf('pin-sha256="%s"', $hash);
        }
        $values[] = sprintf('max-age=%d', $this->config['hpkp']['max-age']);
        if ($this->config['hpkp']['include-sub-domains']) {
            $values[] = 'includeSubDomains';
        }
        if (! empty($this->config['hpkp']['report-uri'])) {
            $values[] = sprintf('report-uri="%s"', $this->config['hpkp']['report-uri']);
        }
        $header = $this->config['hpkp']['report-only']
            ? 'Public-Key-Pins-Report-Only'
            : 'Public-Key-Pins';

        return [$header => implode('; ', $values)];
    }

    /**
     * Get HSTS header.
     *
     * @return array
     */
    private function hsts()
    {
        $hsts = "max-age={$this->config['hsts']['max-age']}";

        if ($this->config['hsts']['include-sub-domains']) {
            $hsts .= '; includeSubDomains';
        }
        if ($this->config['hsts']['preload']) {
            $hsts .= '; preload';
        }

        return ['Strict-Transport-Security' => $hsts];
    }

    /**
     * Get Expected CT header.
     *
     * @return array
     */
    private function ect()
    {
        $ect = "max-age={$this->config['ect']['max-age']}";
        if ($this->config['ect']['enforce']) {
            $ect .= ', enforce';
        }

        if (! empty($this->config['ect']['report-uri'])) {
            $ect .= sprintf(', report-uri="%s"', $this->config['ect']['report-uri']);
        }

        return ['Expect-CT' => $ect];
    }

    /**
     * Get extras headers to add (array_filter will remove header if the value is : an empty string, a scalar 'false' or null).
     *
     * @return array
     */
    private function extras()
    {
        $headers = [];

        foreach ($this->config['extras'] as $header => $value) {
            $headers[ucwords($header, '-')] = $value;
        }

        return array_filter($headers);
    }

    /**
     * Compile HTTP headers.
     */
    public function getCompiledHeaders()
    {
        return array_merge(
            $this->config['settings']['enable-csp'] ? $this->csp() : [],
            $this->config['settings']['enable-hpkp'] ? $this->hpkp() : [],
            $this->config['settings']['enable-hsts'] ? $this->hsts() : [],
            $this->config['settings']['enable-ect'] ? $this->ect() : [],
            $this->config['settings']['enable-extras'] ? $this->extras() : []
        );
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
        $response = $handler->handle($request);
        $headers = $this->getCompiledHeaders();
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    /*
        public static function nonce(): string
        {
            // Algo A
            static $nonce;
            return $nonce ?: $nonce = bin2hex(random_bytes(16));
            // Algo B
            $nonce = base64_encode(random_bytes(18));
        }
    */
}
