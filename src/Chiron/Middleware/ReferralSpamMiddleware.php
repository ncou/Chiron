<?php

declare(strict_types = 1);

namespace Chiron\Middleware;

use ComposerLocator;
use Chiron\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

// TODO : récupérer la liste ici : https://github.com/nabble/semalt-blocker

/*
https://github.com/DougSisk/Laravel-BlockReferralSpam/blob/master/src/Middleware/BlockReferralSpam.php
https://github.com/middlewares/referrer-spam/blob/master/src/ReferrerSpam.php
https://github.com/ARCommunications/Block-Referral-Spam/blob/master/blocker.php
https://github.com/rrodrigonuez/WP-Block-Referrer-Spam/blob/master/wp-block-referrer-spam/controllers/wpbrs-controller-blocker.php
https://github.com/ARCANEDEV/SpamBlocker/blob/master/src/Http/Middleware/BlockReferralSpam.php   +   https://github.com/ARCANEDEV/SpamBlocker/blob/master/src/SpamBlocker.php
https://github.com/wpmaintainer/referer-spam-blocker/blob/master/lib/referer-spam-blocker.php
*/

class ReferralSpamMiddleware implements MiddlewareInterface
{
    /**
     * @var array|null
     */
    private $blackList;
    public function __construct(array $blackList = null)
    {
        $this->blackList = $blackList;
    }
    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('Referer')) {
            if ($this->blackList === null) {
                $this->blackList = self::getBlackList();
            }

            $referer = $request->getHeaderLine('Referer');
            $domain = $this->getUrlDomain($referer);

            if (in_array($domain, $this->blackList)) {
                // TODO : passer une responseFactory en paramétre dans le constructeur
                // TODO : créer une exception pour la code 403 et faire un throw !!!!

                // If a human comes by, don't just serve a blank page
                //echo sprintf("Access to this website has been blocked because your referral '%s' is suspected of spamming.", $domain));
                return new Response(403); // TODO : renvoyer plutot un code 451 non ?
            }
        }
        return $handler->handle($request);
    }


    private function getUrlDomain(string $url) : string
    {
        // Remove all illegal characters from a url
        $url = filter_var($url, FILTER_SANITIZE_URL);

        $host = parse_url($url, PHP_URL_HOST);
        //@TODO : convert the host as punycode for better IDN support
        //$punycode = new Punycode();
        //$url = str_replace($host, $punycode->encode($host), $url);

        return preg_replace('/^(www\.)/i', '', $host);

        // TODO : ajouter un sanity check genre :
        // Sanity check
        /*
        if (filter_var('http://' . $url, FILTER_VALIDATE_URL) === false) {
            throw new Exception("Error with Sanity check");
        }
        */
    }

    /**
     * Returns the piwik's referrer spam blacklist.
     */
    private static function getBlackList(): array
    {
        //$path = ComposerLocator::getPath('piwik/referrer-spam-blacklist').'/spammers.txt';
        //$spammerList = config('app.referral_spam_list_location', base_path('vendor/matomo/referrer-spam-blacklist/spammers.txt'));
        $path = __DIR__ . '/spammers.txt';


        if (!is_file($path)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Unable to locate the piwik referrer spam blacklist file');
            // @codeCoverageIgnoreEnd
        }
        return file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

}
