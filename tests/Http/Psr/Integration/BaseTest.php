<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\StreamFactory;
use Chiron\Http\Factory\UploadedFileFactory;
use Chiron\Http\Factory\UriFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BaseTest extends TestCase
{
    protected function assertNotSameObject($a, $b)
    {
        $this->assertFalse($a === $b, 'Object does not have different references.');
    }

    protected function buildUri($uri)
    {
        $factory = new UriFactory();
        if (! $factory instanceof \Interop\Http\Factory\UriFactoryInterface) {
            throw new \RuntimeException('Constant "URI_FACTORY" must be a reference to a Interop\Http\Factory\UriFactoryInterface');
        }

        return $factory->createUri($uri);
    }

    protected function buildStream($data)
    {
        $factory = new StreamFactory();
        if (! $factory instanceof \Interop\Http\Factory\StreamFactoryInterface) {
            throw new \RuntimeException('Constant "STREAM_FACTORY" must be a reference to a Interop\Http\Factory\StreamFactoryInterface');
        }

        return $factory->createStream($data);
    }

    protected function buildUploadableFile($data)
    {
        $factory = new UploadedFileFactory();
        if (! $factory instanceof \Interop\Http\Factory\UploadedFileFactoryInterface) {
            throw new \RuntimeException('Constant "UPLOADED_FILE_FACTORY" must be a reference to a Interop\Http\Factory\UploadedFileFactoryInterface');
        }

        return $factory->createUploadedFile($data);
    }
}
