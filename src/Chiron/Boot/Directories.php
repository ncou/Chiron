<?php

declare(strict_types=1);

namespace Chiron\Boot;

use InvalidArgumentException;

// TODO : NormalizePath ***************************
//https://github.com/yiisoft/files/blob/0ce2ab3b36fc1dac90d1c1f6dee7882f7c7fbb76/src/FileHelper.php#L107
//https://github.com/composer/composer/blob/78b8c365cd879ce29016884360d4e61350f0d176/src/Composer/Util/Filesystem.php#L473
//https://github.com/thephpleague/flysystem/blob/1426da21dae81e1f3fe1074a166eb6dd3045f810/src/Util.php#L102
//https://github.com/phpstan/phpstan-src/blob/master/src/File/FileHelper.php#L41
//https://github.com/nette/utils/blob/master/src/Utils/FileSystem.php#L158

/**
 * Manage application directories set.
 */
// TODO : permettre d'utiliser les helpers ArrayAccess pour faire un truc du genre "$directories['config']"
final class Directories implements DirectoriesInterface
{
    /** @var array */
    private $directories = [];

    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        foreach ($directories as $name => $directory) {
            $this->set($name, $directory);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, string $path): DirectoriesInterface
    {
        //$path = strtr($path, '\\', '/');
        $path = str_replace(['\\', '//'], '/', $path);
        // TODO : réfléchier si on laisse le '/' à la fin !!!!!
        $this->directories[$name] = rtrim($path, '/') . '/';

        // ou plus simple ===> $path = rtrim(strtr($path, '/\\', '//'), '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException("Undefined directory '{$name}'");
        }

        return $this->directories[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->directories);
    }

    /**
     * {@inheritdoc}
     */
    // TODO : renommer cette méthode en "all()" ????
    // TODO : renommer cette méthode en "toArray()" ????
    public function getAll(): array
    {
        return $this->directories;
    }
}
