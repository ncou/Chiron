<?php

declare(strict_types=1);

namespace Chiron\Http\Parser;

use Psr\Http\Message\ServerRequestInterface;

use function array_shift;
use function explode;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function sprintf;
use function trim;
use const JSON_ERROR_NONE;

class JsonParser implements ParserInterface
{
    public function match(string $contentType) : bool
    {
        $parts = explode(';', $contentType);
        $mime = array_shift($parts);
        return (bool) preg_match('#[/+]json$#', trim($mime));




        // Regex for : 'application/json' or 'application/*+json'
            //if (preg_match('~^application/([a-z.]+\+)?json($|;)~', $mediaType)) {
            //return (bool) preg_match('#[/+]json$#', trim($mime));
            if (preg_match('~application/([a-z.]+\+)?json~', $mediaType)) {

                // Throw error if we are unable to decode body
                //if (is_null($parsed)) throw new BadRequest('Could not deserialize body (Json)');
            }





    }

    public function parse(ServerRequestInterface $request) : ServerRequestInterface
    {
        $rawBody = (string) $request->getBody();
        $parsedBody = json_decode($rawBody, true);
        if (! is_array($parsedBody)) {
            // TODO : on devrait peut etre lever une exception 400 BadRequestHttpException
            $parsedBody = null;
        }
        return $request->withParsedBody($parsedBody);
    }
}
