<?php

declare(strict_types=1);

namespace Chiron\Tests\Boot;

use Chiron\Container\Container;
use Chiron\Logger\LoggerManager;
use Chiron\Boot\Directories;
use InvalidArgumentException;

// TODO : faire des tests en ajoutant une référence circulaire + un test avec un path vide '', et un test en ajoutant un path qu'on supprime ex : @root => 'toto' / @config => '@root/config' et ensuite on remove(@root) et on essaye de faire un get(@config).
// TODO : test pour s'assurer que le slash de fin est bien supprimé, idem pour l'antislash.
// TODO : faire le test ou on fait un get sans le @ en début.

class DirectoriesTest extends \PHPUnit\Framework\TestCase
{
    public function testInit(): void
    {
        $dirs = $this->getDirs(['@foo' => 'bar']);

        $this->assertSame('bar', $dirs->get('@foo'));
    }


    public function testAdd(): void
    {
        $dirs = $this->getDirs();

        $dirs->add(['@foo' => 'bar']);

        $this->assertSame('bar', $dirs->get('@foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Method "Chiron\Boot\Directories::add()" expects an associative array.
     */
    public function testAddInvalidArray(): void
    {
        $dirs = $this->getDirs();

        $dirs->add([true]);
    }


    public function testHas(): void
    {
        $dirs = $this->getDirs(['@root' => 'path', '@empty' => '', 'withoutAtSign' => 'path']);

        $this->assertFalse($dirs->has('@nonExistingAlias'));

        $this->assertTrue($dirs->has('@root'));
        $this->assertTrue($dirs->has('@empty'));

        $this->assertTrue($dirs->has('@withoutAtSign'));
        $this->assertTrue($dirs->has('withoutAtSign'));
    }

    public function testRemove(): void
    {
        $dirs = $this->getDirs(['@root' => 'path', 'withoutAtSign_1' => 'path', 'withoutAtSign_2' => 'path']);

        $dirs->remove('@root');
        $dirs->remove('withoutAtSign_1');
        $dirs->remove('@withoutAtSign_2');

        $this->assertEquals($dirs->all(), []);
    }

    public function testAll(): void
    {
        $expect = ['@root' => 'foo','@base' => 'foo','@config' => 'foo/bar'];

        $dirs = $this->getDirs(['@root' => 'foo', '@base' => '@root', '@config' => '@base/bar']);

        $this->assertEquals($dirs->all(), $expect);
    }


    public function testChainedPaths(): void
    {
        $dirs = $this->getDirs(['@first' => 'path1', '@firstClone' => '@first', '@second' => '@first/path2', '@third' => '@second/path3']);

        $this->assertEquals($dirs->get('@first'), 'path1');
        $this->assertEquals($dirs->get('@firstClone'), 'path1');
        $this->assertEquals($dirs->get('@second'), 'path1/path2');
        $this->assertEquals($dirs->get('@third'), 'path1/path2/path3');
    }

    public function testChainedPathsReverseOrder(): void
    {
        $dirs = $this->getDirs(['@third' => '@second/path3', '@second' => '@first/path2', '@firstClone' => '@first', '@first' => 'path1']);

        $this->assertEquals($dirs->get('@first'), 'path1');
        $this->assertEquals($dirs->get('@firstClone'), 'path1');
        $this->assertEquals($dirs->get('@second'), 'path1/path2');
        $this->assertEquals($dirs->get('@third'), 'path1/path2/path3');
    }






    /**
     * @dataProvider dataProvider
     */
    public function testNormalize(string $alias, string $path, string $expected): void
    {
        $dirs = $this->getDirs();

        $dirs->set($alias , $path);

        $this->assertSame($expected, $dirs->get($alias));
    }

    // TODO : à finir de compléter en ajoutant plus de tests dans ce dataprovider !!!!!
    public function dataProvider(): array
    {
        return [
            ['@alias', 'value', 'value'],
        ];
    }








    protected function getDirs(array $aliases = []): Directories
    {
        $directories = new Directories();
        $directories->init($aliases);

        return $directories;
    }

}
