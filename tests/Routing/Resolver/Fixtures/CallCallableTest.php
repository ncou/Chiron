<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Resolver\Fixtures;

/**
 * Mock object for ControllerResolverTest.
 */
class CallCallableTest
{
    public static $CalledCount = 0;

    public static function __callStatic($name, $arguments)
    {
        static::$CalledCount++;
    }

    public static function toStaticCall()
    {
        static::$CalledCount++;
    }
}
