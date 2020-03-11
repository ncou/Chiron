<?php

declare(strict_types=1);

namespace Chiron\Boot;

use Chiron\Boot\Exception\FileNotFoundException;

//https://github.com/illuminate/filesystem/blob/master/Filesystem.php
//https://github.com/spiral/files/blob/master/src/Files.php

class Filesystem
{
    /**
     * Determine if the given path is a file.
     *
     * @param  string  $filename
     * @return bool
     */
    public function isFile(string $filename): bool
    {
        return is_file($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * Determine if a file or directory is missing.
     *
     * @param  string  $filename
     * @return bool
     */
    public function missing(string $filename): bool
    {
        return ! $this->exists($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $filename): string
    {
        if ($this->isFile($filename)) {
            return file_get_contents($filename);
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function md5(string $filename): string
    {
        if ($this->isFile($filename)) {
            return md5_file($filename);
        }

        throw new FileNotFoundException($filename);
    }

    public function sha1(string $filename): string
    {
        if ($this->isFile($filename)) {
            return sha1_file($filename);
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string  $filename
     * @return string
     */
    public function name(string $filename): string
    {
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param  string  $filename
     * @return string
     */
    public function basename(string $filename): string
    {
        return pathinfo($filename, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param  string  $filename
     * @return string
     */
    public function dirname(string $filename): string
    {
        return pathinfo($filename, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $filename
     * @return string
     */
    public function extension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     *
     * @param  string  $path
     * @param  string  $content
     * @return void
     */
    public function write(string $path,string $content): void
    {
        // If the path already exists and is a symlink, get the real path...
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        // Fix permissions of tempPath because `tempnam()` creates it with permissions set to 0600...
        chmod($tempPath, 0777 - umask());

        file_put_contents($tempPath, $content);

        rename($tempPath, $path);
    }

    /**
     * Get the returned value of a file.
     *
     * @param  string  $filename
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public function getRequire(string $filename)
    {
        if ($this->isFile($filename)) {
            return require $filename;
        }

        throw new FileNotFoundException();
    }


}
