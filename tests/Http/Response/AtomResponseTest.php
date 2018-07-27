<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Response;

use Chiron\Http\Response\AtomResponse;

class AtomResponseTest extends XmlResponseTest
{
    protected $contentType = 'application/atom+xml';

    protected function createResponse($body, int $status = 200, array $headers = [])
    {
        return new AtomResponse($body, $status, $headers);
    }
}
