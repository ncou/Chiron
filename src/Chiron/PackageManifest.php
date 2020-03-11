<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Boot\Directories;
use Chiron\Boot\Filesystem;
use RuntimeException;

final class PackageManifest
{
    /**
     * @var array
     */
    private $manifest;

    /**
     * @var string
     */
    private $runtimeDir;

    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @var string
     */
    private $manifestPath;

    public function __construct(Filesystem $files, Directories $directories)
    {
        $this->files = $files;
        $this->runtimeDir = $directories->get('runtime');
        $this->vendorDir = $directories->get('vendor');

        $this->manifestPath = $this->runtimeDir . '/packages.php';
    }

    // TODO : il faudrait surement aussi faire un @unlink($packagesPath)
    public function clear()
    {
        $this->manifest = null;
        @unlink($this->manifestPath);
    }

    public function build()
    {
        $installedPackages = [];

        if (file_exists($path = $this->vendorDir . '/composer/installed.json')) {
            $installedPackages = json_decode(file_get_contents($path), true);
        }

        $manifest = [];

        foreach ($installedPackages as $package) {
            if (! empty($package['extra']['chiron'])) {
                $packageInfo = $package['extra']['chiron'];

                $manifest[$package['name']] = [];

                if (! empty($packageInfo['providers'])) {
                    $manifest[$package['name']]['providers'] = $packageInfo['providers'];
                }

                if (! empty($packageInfo['aliases'])) {
                    $manifest[$package['name']]['aliases'] = $packageInfo['aliases'];
                }

                if (! empty($packageInfo['bootloaders'])) {
                    $manifest[$package['name']]['bootloaders'] = $packageInfo['bootloaders'];
                }
            }
        }

        $this->write($manifest);
    }

    private function write(array $manifest)
    {
        if (! is_writable($this->runtimeDir)) {
            throw new RuntimeException('The ' . $this->runtimeDir . ' directory must be present and writable.');
        }

        file_put_contents($this->manifestPath, '<?php return ' . var_export($manifest, true) . ';');
    }

    public function providers(): array
    {
        return $this->config('providers');
    }

    public function aliases(): array
    {
        return $this->config('aliases');
    }

    public function bootloaders(): array
    {
        return $this->config('bootloaders');
    }

    private function config(string $key): array
    {
        $manifest = $this->getManifest();

        $data = [];

        foreach ($manifest as $package => $item) {
            if (isset($item[$key])) {
                $data = array_merge($data, (array) $item[$key]);
            }
        }

        return $data;
    }

    /**
     * Get the current package manifest.
     *
     * @return array
     */
    private function getManifest()
    {
        if (! is_null($this->manifest)) {
            return $this->manifest;
        }

        if (! file_exists($this->manifestPath)) {
            $this->build();
        }

        // elle sert à quoi cette ligne ????
        //$this->files->get($this->manifestPath);

        return $this->manifest = file_exists($this->manifestPath) ?
            $this->files->getRequire($this->manifestPath) : [];
    }
}
