<?php

declare(strict_types=1);

namespace Chiron\Handler\Error;

use Psr\Http\Message\ServerRequestInterface;

trait DeterminesContentTypeTrait
{
    /**
     * Known handled content types.
     *
     * @var array
     */
    private $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
        'text/plain',
    ];

    /**
     * Determine which content type we know about is wanted using Accept header.
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Slim's error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    // TODO : autre exemple : https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/core/src/Http/Concerns/DeterminesContentType.php#L42
    // TODO : autre example : https://github.com/franzliedke/whoops-middleware/blob/master/src/FormatNegotiator.php#L28
    protected function determineContentType(ServerRequestInterface $request): string
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        $count = count($selectedContentTypes);
        if ($count) {
            $current = current($selectedContentTypes);
            /*
             * Ensure other supported content types take precedence over text/plain
             * when multiple content types are provided via Accept header.
             */
            if ($current === 'text/plain' && $count > 1) {
                return next($selectedContentTypes);
            }

            return $current;
        }
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }

}
