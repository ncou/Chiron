<?php

declare(strict_types=1);

namespace Chiron\Middleware;

//https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/_htaccess_versions/htaccess-mod_rewrite.txt

use Chiron\Http\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAgentBlockerMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $badAgents = [];

    public function loadBadAgentsListFromArray(array $badAgents): self
    {
        $this->badAgents = $badAgents;

        return $this;
    }

    public function loadBadAgentsListFromFile(string $pathFile): self
    {
        if (! is_file($pathFile)) {
            throw new InvalidArgumentException('Unable to locate the bad user-agents blacklist file.');
        }
        $this->badAgents = file($pathFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        if ($this->isBadAgent($userAgent)) {
            // TODO : passer une responseFactory en paramétre dans le constructeur
            // TODO : créer une exception pour la code 403 et faire un throw !!!!

            // If a human comes by, don't just serve a blank page
            //echo sprintf("Access to this website has been blocked because your user-agent '%s' is suspected of spamming.", $userAgent));
            return new Response(403); // TODO : renvoyer plutot un code 451 non ?
        }

        return $handler->handle($request);
    }

    /*
     * Compile the regex patterns into one regex string.
     *
     * @param array
     *
     * @return string
     */
    //https://github.com/JayBizzle/Crawler-Detect/blob/master/src/CrawlerDetect.php

    public function isBadAgent(string $userAgent): bool
    {
        // protect the specials characters that are used in the regular expression syntax
        $badAgents = array_map(function ($value) {
            return preg_quote($value, '/');
        }, $this->badAgents);
        // create a large regex with all the values to search
        $pattern = '(' . implode('|', $badAgents) . ')';
        // search the bad bots ! (result is : 0, 1 or FALSE if there is an error)
        $result = preg_match('/' . $pattern . '/i', $userAgent);

        return (bool) $result;
    }

    /*
    //https://github.com/JayBizzle/Crawler-Detect/blob/master/src/CrawlerDetect.php
        $this->compiledRegex = $this->compileRegex($this->crawlers->getAll());
    
    
        $result = preg_match('/'.$this->compiledRegex.'/i', trim($agent), $matches);
       return (bool) $result;
    */
}
