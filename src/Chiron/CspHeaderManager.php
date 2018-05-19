<?php

declare(strict_types=1);

namespace Chiron;

use Psr\Http\Message\ResponseInterface;

/**
 * Manage the CSP Header.
 */
class CspHeaderManager
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

    private $csp = [];

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $cspSettings = [])
    {
        $this->csp = $cspSettings;
    }

    /**
     * PSR-7 header injection.
     *
     * This will inject the header into your PSR-7 object. (Request, Response,
     * etc.) This method returns an instance of whatever you passed, so long
     * as it implements MessageInterface.
     *
     * @param \Psr\Http\ResponseInterface $response
     *
     * @return \Psr\Http\ResponseInterface
     */
    public function injectCSPHeader(ResponseInterface $response): ResponseInterface
    {
        $headers = $this->compileCsp();
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    /**
     * Get CSP header.
     *
     * @return array
     */
    private function compileCsp()
    {
        $values = [];
        foreach (self::CSP_DIRECTIVES as $directive) {
            if (isset($this->csp[$directive])) {
                $values[] = $this->compileCspDirective($directive, $this->csp[$directive]);
            }
        }
        if (! empty($this->csp['block-all-mixed-content'])) {
            $values[] = 'block-all-mixed-content';
        }
        if (! empty($this->csp['upgrade-insecure-requests'])) {
            $values[] = 'upgrade-insecure-requests';
        }
        if (! empty($this->csp['require-sri-for'])) {
            $values[] = sprintf('require-sri-for %s', $this->csp['require-sri-for']);
        }
        if (! empty($this->csp['report-uri'])) {
            $values[] = sprintf('report-uri %s', $this->csp['report-uri']);
            // support new navigator wich use now "report-to" instead of "report-uri"
            $values[] = sprintf('report-to %s', $this->csp['report-uri']);
        }
        // TODO : lever une exception si on utilise report-only mais que le report-uri n'est pas défini car on va consommer du CPU pour rien au final !!!!!!
        $header = ! empty($this->csp['report-only'])
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
                    // TODO : il faudrait visiblement aussi  ajouter les caractéres - et _ dans la liste du regex pour le preg_replace !!!!
                    $ret[] = sprintf("'%s-%s'", $algo, preg_replace('~[^A-Za-z0-9+/=]~', '', $hashval));
                    //$ret[] = sprintf("'%s-%s'", preg_replace('~[^A-Za-z0-9]~', '', $algo), preg_replace('~[^A-Za-z0-9+/=]~', '', $hashval));
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
     * Add a new nonce to the existing CSP. Returns the nonce generated.
     *
     * @param string $directive
     * @param string $nonce     (if empty, it will be generated)
     *
     * @return string
     */
    public function nonce(string $directive = 'script-src', string $nonce = ''): string
    {
        if (! \in_array($directive, self::CSP_DIRECTIVES)) {
            return '';
        }
        if (empty($nonce)) {
            $nonce = bin2hex(random_bytes(16));
        }
        $this->csp[$directive]['nonces'][] = $nonce;

        return $nonce;
    }

    /**
     * Add a new hash to the existing CSP.
     *
     * @param string $directive
     * @param string $script
     * @param string $algorithm
     *
     * @return self
     */
    public function hash(
        string $directive = 'script-src',
        string $script = '',
        string $algorithm = 'sha384'
    ): self {
        if (\in_array($directive, self::CSP_DIRECTIVES)) {
            $this->csp[$directive]['hashes'][$algorithm][] = base64_encode(\hash($algorithm, $script, true));
        }

        return $this;
    }

    /**
     * Add a new (pre-calculated) base64-encoded hash to the existing CSP.
     *
     * @param string $directive
     * @param string $hash
     * @param string $algorithm
     *
     * @return self
     */
    public function preHash(
        string $directive = 'script-src',
        string $hash = '',
        string $algorithm = 'sha384'
    ): self {
        if (\in_array($directive, self::CSP_DIRECTIVES)) {
            $this->csp[$directive]['hashes'][$algorithm][] = $hash;
        }

        return $this;
    }
}
