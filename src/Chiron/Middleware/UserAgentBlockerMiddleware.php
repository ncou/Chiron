<?php

declare(strict_types = 1);

namespace Chiron\Middleware;

//https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/_htaccess_versions/htaccess-mod_rewrite.txt

use Chiron\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class UserAgentBlockerMiddleware implements MiddlewareInterface
{

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $badAgents = [
            'facebookexternalhit/1.1',
            'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            'Facebot',
            'Twitterbot',
        ];

        $userAgent = $request->getHeaderLine('User-Agent');

        if (in_array($userAgent, $badAgents)) {
            // TODO : passer une responseFactory en paramétre dans le constructeur
            // TODO : créer une exception pour la code 403 et faire un throw !!!!

            // If a human comes by, don't just serve a blank page
            //echo sprintf("Access to this website has been blocked because your user-agent '%s' is suspected of spamming.", $userAgent));
            return new Response(403); // TODO : renvoyer plutot un code 451 non ?
        }

        return $handler->handle($request);
    }

    /**
     * Compile the regex patterns into one regex string.
     *
     * @param array
     *
     * @return string
     */
    //https://github.com/JayBizzle/Crawler-Detect/blob/master/src/CrawlerDetect.php
    /*
    public function compileRegex($patterns)
    {
        return '('.implode('|', $patterns).')';
    }*/


/*
//https://github.com/JayBizzle/Crawler-Detect/blob/master/src/CrawlerDetect.php
    $this->compiledRegex = $this->compileRegex($this->crawlers->getAll());


    $result = preg_match('/'.$this->compiledRegex.'/i', trim($agent), $matches);
   return (bool) $result;
*/

}
