<?php

declare(strict_types=1);

namespace Chiron\Tests\Boot;

use Chiron\Container\Container;
use Chiron\Logger\LoggerManager;
use Chiron\Boot\Environment;
use InvalidArgumentException;

class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    public function testInit(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $this->assertSame('value', $env->get('key'));
    }

    public function testDefault(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $this->assertSame('default', $env->get('other', 'default'));
    }

    public function testHas(): void
    {
        $env = $this->getEnv(['key' => 'value']);

        $this->assertFalse($env->has('other'));
        $this->assertTrue($env->has('key'));
    }

    public function testAll(): void
    {
        $expect = array_merge($_SERVER, $_ENV, ['foo' => 'bar']);

        $env = $this->getEnv(['foo' => 'bar']);

        $this->assertEquals($expect, $env->all());
    }

    public function testAdd(): void
    {
        $env = $this->getEnv();

        $env->add(['foo' => 'bar']);

        $this->assertSame('bar', $env->get('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Method "Chiron\Boot\Environment::add()" expects an associative array.
     */
    public function testAddInvalidArray(): void
    {
        $env = $this->getEnv();

        $env->add([true]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testNormalize(string $value, $expected): void
    {
        $env = $this->getEnv();

        $env->set('key' , $value);

        $this->assertSame($expected, $env->get('key'));
    }

    public function dataProvider(): array
    {
        return [
            ['', ''],
            ['false', false],
            ['FALSE', false],
            ['(false)', false],
            ['true', true],
            ['TRUE', true],
            ['(true)', true],
            ['null', null],
            ['NULL', null],
            ['(null)', null],
            ['empty', ''],
            ['EMPTY', ''],
            ['(empty)', ''],
            ['"hello"', 'hello'],
            ["'hello'", 'hello'],
            ['123', '123'],
            ['123.4', '123.4'],
            ['FooBAR', 'FooBAR'],
        ];
    }


    protected function getEnv(array $values = []): Environment
    {
        $environment = new Environment();
        $environment->init($values);

        return $environment;
    }

}
