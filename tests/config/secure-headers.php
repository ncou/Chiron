<?php

return [
    /*
     * X-Content-Type-Options
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
     *
     * Available Value: 'nosniff'
     */
    'x-content-type-options' => 'nosniff',
    /*
     * X-Frame-Options
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
     *
     * Available Value: 'deny', 'sameorigin', 'allow-from <uri>'
     */
    'x-frame-options' => 'sameorigin',
    /*
     * X-XSS-Protection
     *
     * Reference: https://blogs.msdn.microsoft.com/ieinternals/2011/01/31/controlling-the-xss-filter
     *
     * Available Value: '1', '0', '1; mode=block'
     */
    'x-xss-protection' => '1; mode=block',
    /*
     * Referrer-Policy
     *
     * Reference: https://w3c.github.io/webappsec-referrer-policy
     *
     * Available Value: 'no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin',
     *                  'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url'
     */
    'referrer-policy' => 'no-referrer, strict-origin-when-cross-origin',
    /*
     * HTTP Strict Transport Security
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/HTTP_strict_transport_security
     *
     * Please ensure your website had set up ssl/tls before enable hsts.
     * HSTS will be ignored if 'enable' is false.
     * Note : the experimental chrome flag 'preload' is automaticly used, register your site here : https://hstspreload.org/
     */
    'hsts' => [
        'enable'              => false,
        'max-age'             => 63072000,
        'include-sub-domains' => true,
        'preload'             => true,
    ],
    /*
     * Expect-CT
     *
     * Reference: https://tools.ietf.org/html/draft-ietf-httpbis-expect-ct-02
     *
     * Expect-CT will be ignored if 'enable' is false.
     */
    'ect' => [
        'enable'     => false,
        'enforce'    => false,
        'max-age'    => 63072000,
        'report-uri' => null,
    ],
    /*
     * Public Key Pinning
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/Public_Key_Pinning
     *
     * hpkp will be ignored if hashes is empty.
     */
    'hpkp' => [
        'hashes' => [
            // 'sha256-hash-value',
        ],
        'include-sub-domains' => true,
        'max-age'             => 63072000,
        'report-only'         => false,
        'report-uri'          => '',
    ],
    /*
     * Content Security Policy
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/CSP
     *
     * csp will be ignored if custom-csp is not null. To disable csp, set custom-csp to empty string.
     *
     * Note: custom-csp does not support report-only.
     */
    'custom-csp' => null,
    'csp'        => [
        'report-only' => false,
        'report-uri'  => '', // uri used for the report when 'report-only' is activated
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/require-sri-for
        'require-sri-for' => '', // could be : 'script' / 'style' / 'script style'
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/block-all-mixed-content
        'block-all-mixed-content'   => false,
        'upgrade-insecure-requests' => false,
        /*
         * Note: when directive value is empty, it will use `none` for that directive.
         */
        'script-src' => [
            'allow' => [
                // url,
            ],
            'hashes' => [
                // 'sha256' => [
                //     'hash-value',
                // ],
            ],
            'nonces' => [
                // base64-encoded,
            ],
            'schemes' => [
                // The colon character at the end is MANDATORY !
                // 'http:', 'https:', 'blob:', 'data:', 'mediastream:', 'filesystem:'
            ],
            'self'                     => false,
            'unsafe-inline'            => false,
            'unsafe-eval'              => false,
            'strict-dynamic'           => false,
            'unsafe-hashed-attributes' => false,
            'report-sample'            => false,
        ],
        'style-src' => [
            'allow' => [
                // url,
            ],
            'hashes' => [
                // 'sha256' => [
                //     'hash-value',
                // ],
            ],
            'nonces' => [
                // base64-encoded,
            ],
            'schemes' => [
                // 'https:',
            ],
            'self'          => false,
            'unsafe-inline' => false,
        ],
        'img-src' => [
            //
        ],
        'default-src' => [
            //
        ],
        'base-uri' => [
            //
        ],
        'connect-src' => [
            //
        ],
        'font-src' => [
            //
        ],
        'form-action' => [
            //
        ],
        'frame-ancestors' => [
            //
        ],
        'frame-src' => [
            //
        ],
        'manifest-src' => [
            //
        ],
        'media-src' => [
            //
        ],
        'object-src' => [
            //
        ],
        'worker-src' => [
            //
        ],
        'plugin-types' => [
            // one or more mime type : 'application/x-shockwave-flash', 'application/pdf'
        ],
        // uncomment the line '' to have a default sandbox flag. uncomment other lines to add some exeptions in the sandbox restriction rules
        'sandbox' => [
            //'',
            //'allow-forms',
            //'allow-modals',
            //'allow-orientation-lock',
            //'allow-pointer-lock',
            //'allow-popups',
            //'allow-popups-to-escape-sandbox',
            //'allow-presentation',
            //'allow-same-origin',
            //'allow-scripts',
            //'allow-top-navigation'
        ],
    ],
];
