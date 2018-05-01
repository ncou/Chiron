<?php

return [
    /*
     * Apply user settings for the security middleware
     *
     * You can enable or disable the 5 major modules (Extras security headers, HSTS, ECT, HPKP, CSP)
     *
     */
    'settings' => [
        'enable-extras' => true,
        'enable-hsts'   => false,
        'enable-ect'    => false,
        'enable-hpkp'   => false,
        'enable-csp'    => false,
    ],
    /*
     * Extras Headers for a better security
     *
     * It's the major knowed headers to improve you website security.
     * You can add you own headers using a principle of "key = value" for "headername = headervalue".
     * The header name will be camelCased, you just need to ensure the header name exist in the http spec
     * example, you can add in the array : 'x-permitted-cross-domain-policies' => 'none'
     */
    'extras' => [
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
    ],
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
        'max-age'             => 63072000,
        'include-sub-domains' => true,
        'preload'             => true,
    ],
    /*
     * Expect-CT
     *
     * Reference: https://tools.ietf.org/html/draft-ietf-httpbis-expect-ct-02
     *
     */
    'ect' => [
        'enforce'    => false,
        'max-age'    => 63072000,
        'report-uri' => null,
    ],
    /*
     * Public Key Pinning
     *
     * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/Public_Key_Pinning
     *
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
     */
    'csp'        => [
        'report-only' => false,
        'report-uri'  => '', // uri used for the report when 'report-only' is activated
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/require-sri-for
        'require-sri-for' => '', // could be : 'script' / 'style' / 'script style'
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/block-all-mixed-content
        'block-all-mixed-content'   => false,
        'upgrade-insecure-requests' => false,
        /*
         * Note: when directive value is empty, it will use 'none' for that directive.
         */
        'script-src' => [
            'allow' => [
                // url,
            ],
            'hashes' => [
                'sha256' => [
                    //     'hash-value',
                ],
                'sha384' => [
                    //     'hash-value',
                ],
                'sha512' => [
                    //     'hash-value',
                ],
            ],
            'nonces' => [
                // base64-encoded,
            ],
            'schemes' => [
                // uncomment the wanted scheme. but keep the colon character at the end, because it's MANDATORY !!!
                // 'http:',
                // 'https:',
                // 'blob:',
                // 'data:',
                // 'mediastream:',
                // 'filesystem:'
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
        // uncomment the line '' to have a default sandbox header (with all the restriction).
        // uncomment other lines to add some exceptions in the sandbox restrictions rules.
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
