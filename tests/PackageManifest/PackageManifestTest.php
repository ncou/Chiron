<?php

declare(strict_types=1);

namespace Chiron\Tests\PackageManifest;

use Chiron\Boot\Directories;
use Chiron\Filesystem\Filesystem;
use Chiron\Composer\PackageManifest;

class PackageManifestTest extends \PHPUnit\Framework\TestCase
{
    public function testAssetLoading()
    {
        @unlink(__DIR__ . '/fixtures/packages.php');

        $dirs = new Directories([]);
        $dirs->set('@vendor', __DIR__ . '/fixtures/vendor');
        $dirs->set('@runtime', __DIR__ . '/fixtures');
        $manifest = new PackageManifest(new Filesystem(), $dirs);

        $this->assertEquals(['foo', 'bar', 'baz'], $manifest->providers());
        $this->assertEquals(['Foo' => 'Foo\\Facade'], $manifest->aliases());

        unlink(__DIR__ . '/fixtures/packages.php');
    }
}
