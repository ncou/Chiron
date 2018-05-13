<?php

declare(strict_types=1);

namespace Chiron\Middleware;

use Chiron\Http\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : récupérer la liste ici : https://github.com/nabble/semalt-blocker
//https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/_htaccess_versions/htaccess-mod_rewrite.txt

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
     * @var array
     */
    private $blackList = [];

    public function loadBlackListFromArray(array $blackListed): self
    {
        $this->blackList = $blackListed;

        return $this;
    }

    public function loadBlackListFromFile(string $pathFile): self
    {
        if (! is_file($pathFile)) {
            throw new InvalidArgumentException('Unable to locate the referrer spam blacklist file.');
        }
        $this->blackList = file($pathFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('Referer')) {
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

    private function getUrlDomain(string $url): string
    {
        //$scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        // Strip the 'www.' to get only the full domain name
        $domain = preg_replace('/^(www\.)/i', '', $host);
        // Encode the international domain as punycode
        $domain = idn_to_ascii($domain);
        // Sanitize the result ascii domain
        $domain = filter_var($domain, FILTER_SANITIZE_URL);

        return $domain;
    }
}
