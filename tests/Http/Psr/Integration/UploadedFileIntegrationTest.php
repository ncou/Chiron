<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\UploadedFileFactory;
use Psr\Http\Message\UploadedFileInterface;

/**
 * TODO Write me.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class UploadedFileIntegrationTest extends BaseTest
{
    /**
     * @var array with functionName => reason
     */
    protected $skippedTests = [];

    /**
     * @var UploadedFileInterface
     */
    private $uploadedFile;

    /**
     * @return UploadedFileInterface that is used in the tests
     */
    public function createSubject()
    {
        return (new UploadedFileFactory())->createUploadedFile('writing to tempfile');
    }

    protected function setUp()
    {
        $this->uploadedFile = $this->createSubject();
    }

    public function testNothing()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->assertTrue(true);
    }
}
