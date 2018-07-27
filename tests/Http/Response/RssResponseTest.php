<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Response;

use Chiron\Http\Response\RssResponse;

class RssResponseTest extends XmlResponseTest
{
    protected $contentType = 'application/rss+xml';

    protected function createResponse($body, int $status = 200, array $headers = [])
    {
        return new RssResponse($body, $status, $headers);
    }
}
