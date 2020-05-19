<?php

declare(strict_types=1);

namespace Chiron\Boot;

use InvalidArgumentException;

//https://github.com/yiisoft/aliases/blob/master/src/Aliases.php

// TODO : NormalizePath ***************************
//https://github.com/yiisoft/files/blob/0ce2ab3b36fc1dac90d1c1f6dee7882f7c7fbb76/src/FileHelper.php#L107
//https://github.com/composer/composer/blob/78b8c365cd879ce29016884360d4e61350f0d176/src/Composer/Util/Filesystem.php#L473
//https://github.com/thephpleague/flysystem/blob/1426da21dae81e1f3fe1074a166eb6dd3045f810/src/Util.php#L102
//https://github.com/phpstan/phpstan-src/blob/master/src/File/FileHelper.php#L41
//https://github.com/nette/utils/blob/master/src/Utils/FileSystem.php#L158

//Exemple avec une classe Directory qui permet de normalizer un fichier + d'obtenir le remin relatif par rapport à un fichier
//https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3e5dd53a0ab1fbe87057b06a027d8d072457ca3e/src/Cache/Directory.php#L49

/**
 * Manage application directories set.
 */
// TODO : permettre d'utiliser les helpers ArrayAccess pour faire un truc du genre "$directories['config']"
final class Directories
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

    public function set(string $name, string $path): self
    {
        //$path = strtr($path, '\\', '/');
        $path = str_replace(['\\', '//'], '/', $path);
        // TODO : réfléchier si on laisse le '/' à la fin !!!!!
        $this->directories[$name] = rtrim($path, '/') . '/';

        // ou plus simple ===> $path = rtrim(strtr($path, '/\\', '//'), '/'); ou encore : $dir = str_replace('\\', '/', $dir);

        // rtrim($test, "\/");
        //rtrim(strtr($mask, '\\', '/'), '/');

        return $this;
    }

    public function get(string $name): string
    {
        if (! $this->has($name)) {
            // TODO : créer une classe DirectoryException ????
            throw new InvalidArgumentException("Undefined directory '{$name}'");
        }

        return $this->directories[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->directories);
    }

    // TODO : renommer cette méthode en "all()" ????
    // TODO : renommer cette méthode en "toArray()" ????
    public function getAll(): array
    {
        return $this->directories;
    }

    /**
     * Replace @root/path with the 'root' directory.
     *
     * @param string $target
     *
     * @return string
     */
    // TODO : attention il y a un risque que cela ne fonctionne pas car on ajoute un '/' à la fin du directory, donc si on remplace '@root/test' la valeur @root vaudra surement '/exemple/' et donc on aura deux '//' avec un résultat du genre : '/exemple//test'
    public function path(string $target): string
    {
        foreach ($this->directories as $alias => $value) {
            $target = str_replace("@{$alias}", $value, $target);
        }

        return $target;
    }

    /**
     * Normalizes given directory names by removing trailing slashes.
     *
     * Excluding: (s)ftp:// wrapper
     *
     * @param string $dir
     *
     * @return string
     */
    // TODO : utiliser cette fonction !!!!
    // TODO : virer le cas du FTP car cela ne nous servira pas !!!!
    /*
    private function normalizeDir($dir)
    {
        $dir = rtrim($dir, '/'.\DIRECTORY_SEPARATOR);

        if (preg_match('#^s?ftp://#', $dir)) {
            $dir .= '/';
        }

        return $dir;
    }*/

/*
    public function normalizeDir($dir) {
        $dir = str_replace("\\", "/", $dir);
        if($dir != "/") {
            $dir = rtrim($dir, "/")."/";
        }
        return $dir;
    }
    */

    /**
     * Normalize reference to directories.
     *
     * @param  string path to directory
     *
     * @return string normalized path to directory
     */
    /*
    public static function normalizeDir($sDir)
    {
        $sDir = str_replace(DIRECTORY_SEPARATOR, '/', $sDir);
        if (substr($sDir, -1) != '/') {
            $sDir.= '/';
        }
        return $sDir;
    }*/

/*
    static public function normalizeDir($dirpath){
        if(substr($dirpath,-1) != '/'){
            $dirpath.='/';
        }
        return $dirpath;
    }
*/

    /**
     * Normalizes dir by adding missing trailing slash.
     *
     * @param string $sDir Directory
     *
     * @return string
     */
    /*
    public function normalizeDir($sDir)
    {
        if (isset($sDir) && $sDir != "" && substr($sDir, -1) !== '/') {
            $sDir .= "/";
        }

        return $sDir;
    }*/

    /**
     * Normalizes given directory names by removing trailing slashes.
     *
     * Excluding: (s)ftp:// or ssh2.(s)ftp:// wrapper
     */
    /*
    private function normalizeDir(string $dir): string
    {
        if ('/' === $dir) {
            return $dir;
        }

        $dir = rtrim($dir, '/'.\DIRECTORY_SEPARATOR);

        if (preg_match('#^(ssh2\.)?s?ftp://#', $dir)) {
            $dir .= '/';
        }

        return $dir;
    }*/
}
