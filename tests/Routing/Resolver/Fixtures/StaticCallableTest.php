<?php

declare(strict_types=1);

namespace Chiron\Tests\Routing\Resolver\Fixtures;

/**
 * Mock object for ControllerResolverTest.
 */
class StaticCallableTest
{
    public static $CalledCount = 0;

    public static function toStaticCall()
    {
        static::$CalledCount++;
    }
}
