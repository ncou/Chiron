<?php

declare(strict_types=1);

namespace Chiron\Core;

use Chiron\Container\SingletonInterface;
use InvalidArgumentException;

//https://github.com/swoft-cloud/swoft-framework/blob/master/src/Concern/PathAliasTrait.php

//https://github.com/yiisoft/aliases/blob/master/src/Aliases.php
//https://github.com/yiisoft/aliases/blob/master/tests/AliasesTest.php

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
// TODO : permettre de faire un getIterator sur cette classe, pour utiliser cette classe comme un tableau !!!!
// TODO : permettre d'utiliser les helpers ArrayAccess pour faire un truc du genre "$directories['config']"
final class Directories implements SingletonInterface
{
    /** @var array */
    private $aliases = [];

    public function init(array $paths = [])
    {
        $this->aliases = [];
        $this->add($paths);
    }

    /**
     * @param array $paths An array with directories aliases as keys and paths as values
     */
    public function add(array $paths): void
    {
        foreach ($paths as $alias => $path) {
            if (! is_string($alias)) {
                throw new InvalidArgumentException(sprintf('Method "%s()" expects an associative array.', __METHOD__));
            }
            $this->set($alias, $path);
        }
    }

    /**
     * Register directory alias.
     *
     * @param string $alias Targeted directory alias
     * @param string $path  Targeted directory path
     */
    // TODO : il faut virer le trailing slash de fin de chaine ('/' ou '\')
    // TODO : attention si on passe un $alias à chaine vide cela va péter !!!! Lever une exception, idem si $path est vide car ca n'a pas de sens !!!
    // TODO : lever une exception si dans $alias on trouve un spéarateur "/" ou "\" car on doit pas pouvoir enregistrer un alias du type "@root/config" => "xxx/xxx"
    public function set(string $alias, string $path): void
    {
        // TODO : lever une erreur si la chaine $path est vide. non ??? idem pour $alias ou si $alias est uniquement un caractére '@' ou si il y a des alias de '@root/folder'. Il faudrait surement aussi virer les slash et antislash (\/) à la fin du paramétre $path.

        if (! self::isAlias($alias)) {
            $alias = '@' . $alias;
        }

        // TODO : déplacer ce bout de code dans une méthode privée normalizeDir() ????
        //$path = str_replace('\\', '/', $path);
        //$path = rtrim($path, '/') . '/';

        $path = self::normalizeDir($path);

        $this->aliases[$alias] = $path;
    }

    /**
     * Returns all path aliases translated into an actual paths.
     *
     * @return array Actual paths indexed by alias name.
     */
    public function all(): array
    {
        $result = [];
        foreach ($this->aliases as $alias => $path) {
            // resolve the alias path.
            $result[$alias] = $this->get($path);
        }

        return $result;
    }

    /**
     * Get directory alias real path.
     *
     * @param string $alias
     *
     * @return string Associated path for the given alias
     */
    // TODO : on doit faire quoi si l'utilisateur fait un get d'un alias vide ??? On léve une exception InvalidArgument ???
    // TODO : faire un normalizePath() pour résoudre les chemins du style './../xxxx'
    public function get(string $alias): string
    {
        if (! self::isAlias($alias)) {
            return $alias;
        }

        $alias = strtr($alias, '\\', '/');

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (! isset($this->aliases[$root])) {
            throw new InvalidArgumentException(sprintf('Invalid directory path alias "%s".', $root));
        }
        // use method get() to resolve chained aliases.
        $rootPath = $this->get($this->aliases[$root]);
        // remove trailing slashes in chained aliases.
        $rootPath = $pos === false ? $rootPath : rtrim($rootPath, '/');

        return str_replace($root, $rootPath, $alias);
    }

    /**
     * Whether the alias exists.
     *
     * @param string $alias
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    // TODO : lever une erreur si l'utilisateur essaye de faire un has() sur une chaine vide ou égale à @ ????
    // TODO : lever une erreur si l'utilisateur essaye de faire un has() sur un alias+chemin. exemple : ->has('@root/runtime')
    public function has(string $alias): bool
    {
        if (! self::isAlias($alias)) {
            $alias = '@' . $alias;
        }

        return isset($this->aliases[$alias]);
    }

    /**
     * Remove alias.
     *
     * @param string $alias
     */
    // TODO : lever une erreur si l'utilisateur essaye de faire un remove() sur une chaine vide ou égale à @ ????
    // TODO : lever une erreur si l'utilisateur essaye de faire un remove() sur un alias+chemin. exemple : ->remove('@root/runtime')
    public function remove(string $alias): void
    {
        if (! self::isAlias($alias)) {
            $alias = '@' . $alias;
        }

        if ($this->aliases[$alias]) {
            unset($this->aliases[$alias]);
        }
    }

    /**
     * Aliases should start with an '@' character.
     *
     * @param string $alias
     *
     * @return bool
     */
    private static function isAlias(string $alias): bool
    {
        return strncmp($alias, '@', 1) === 0;
    }

    /**
     * Normalize reference to directories.
     * Enforce a trailing slash.
     *
     * @param string $dir path to directory
     *
     * @return string normalized path to directory
     */
    // TODO : déplacer cette méthode dans la classe Path::class ???? et l'utiliser aussi dans la classe Framework->path() pour normaliser le chemin du répertoire !!!!
    private static function normalizeDir($dir): string
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, '/', $dir);

        if (substr($dir, -1) !== '/') {
            $dir .= '/';
        }

        return $dir;
    }

    /*
        public function exists(string $alias): bool
        {
            return file_exists($this->get($alias));
        }

        public function writable(string $alias): bool
        {
            return is_writable($this->get($alias));
        }
    */

    // TODO : retourner un chemin relatif (cad en supprimant le début du chemin qui correspond au chemin '@root')
    public function relative(string $path, string $baseDir = '@root'): bool
    {
        // exemple : return self::formatPath($directories->get('logs'), $directories->get('root'));
    }

    // TODO : utiliser la méthode Path::relativePath() ou Filesystem::relativePath()
    private static function formatPath(string $path, string $baseDir): string
    {
        return preg_replace('~^' . preg_quote($baseDir, '~') . '~', '.', $path);
    }

    public function set_OLD(string $alias, string $path): void
    {
        //$path = strtr($path, '\\', '/');
        $path = str_replace(['\\', '//'], '/', $path);
        //$path = str_replace(['\\', '/'], '/', $path);
        // TODO : réfléchier si on laisse le '/' à la fin !!!!!
        $this->paths[$alias] = rtrim($path, '/') . '/';

        // ou plus simple ===> $path = rtrim(strtr($path, '/\\', '//'), '/'); ou encore : $dir = str_replace('\\', '/', $dir);

        // rtrim($test, "\/");
        //rtrim(strtr($mask, '\\', '/'), '/');

        //return $this;
    }

    public function get_OLD(string $alias): string
    {
        if (! $this->has($alias)) {
            // TODO : créer une classe DirectoryException ????
            // TODO : lever plutot une ApplicationException !!!! + faire un sprintf pour le message
            throw new InvalidArgumentException("Undefined directory '{$alias}'");
        }

        return $this->paths[$alias];
    }

    public function has_OLD(string $alias): bool
    {
        return isset($this->paths[$alias]);
    }

    public function paths_OLD(): array
    {
        return $this->paths;
    }

    /**
     * Replace @root/path with the 'root' directory.
     *
     * @param string $target
     *
     * @return string
     */
    // TODO : attention il y a un risque que cela ne fonctionne pas car on ajoute un '/' à la fin du directory, donc si on remplace '@root/test' la valeur @root vaudra surement '/exemple/' et donc on aura deux '//' avec un résultat du genre : '/exemple//test'
    public function path_OLD(string $target): string
    {
        foreach ($this->paths as $alias => $value) {
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
