<?php

declare(strict_types=1);

namespace Chiron\Config;

interface InjectableInterface
{
    public function getConfigSection(): string;

    public function inject(array $config): void;
}
