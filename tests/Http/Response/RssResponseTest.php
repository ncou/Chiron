<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Response;

use Chiron\Http\Response\RssResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use const PHP_EOL;

class RssResponseTest extends XmlResponseTest
{
    protected $contentType = 'application/rss+xml';

    protected function createResponse($body,int $status = 200,array $headers = [])
    {
        return new RssResponse($body, $status, $headers);
    }
}
