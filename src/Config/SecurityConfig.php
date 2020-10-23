<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Closure;

final class SecurityConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'security';

    public const KEY_BYTES_SIZE = 32;

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'key' => Expect::xdigit()->assert(Closure::fromCallable([$this, 'assertKeyLength']), 'length = 64 characters')->default(env('APP_KEY')),
        ]);
    }

    public function getKey(): string
    {
        return $this->get('key');
    }

    public function getRawKey(): string
    {
        return hex2bin($this->getKey());
    }

    /**
     * Length of the key should be twice (x2) the bytes size because it's hexa encoded.
     */
    private function assertKeyLength(string $value): bool
    {
        return strlen($value) === self::KEY_BYTES_SIZE * 2;
    }
}
