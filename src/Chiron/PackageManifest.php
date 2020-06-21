<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Boot\Directories;
use Chiron\Boot\Filesystem;
use RuntimeException;

// TODO : on devrait aussi gérer les "inflectors" (c'est les mutations) à ajouter au container.
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

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem, Directories $directories)
    {
        $this->filesystem = $filesystem;
        // TODO : utiliser plutot le répertoire cache plutot que 'runtime'
        $this->runtimeDir = $directories->get('@runtime');
        $this->vendorDir = $directories->get('@vendor');

        // TODO : attention le directories ajoute d'office un '/' à la fin du répertoire, donc ici on aura deux fois '//packages.php'. A corriger !!!!
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

        // TODO : attention le directories ajoute d'office un '/' à la fin du répertoire, donc ici on aura deux fois '//composer....etc'. A corriger !!!!
        if (file_exists($path = $this->vendorDir . '/composer/installed.json')) {
            $installedPackages = json_decode(file_get_contents($path), true);
        }

        $manifest = [];

        foreach ($installedPackages as $package) {
            if (! empty($package['extra']['chiron'])) {
                $packageInfo = $package['extra']['chiron'];

                $manifest[$package['name']] = [];

                // TODO : améliorer le code en le factorisant, il y a 4 fois le même bout de code pour un nom de balise différent !!!!
                if (! empty($packageInfo['providers'])) {
                    $manifest[$package['name']]['providers'] = $packageInfo['providers'];
                }

                if (! empty($packageInfo['aliases'])) {
                    $manifest[$package['name']]['aliases'] = $packageInfo['aliases'];
                }

                if (! empty($packageInfo['bootloaders'])) {
                    $manifest[$package['name']]['bootloaders'] = $packageInfo['bootloaders'];
                }

                if (! empty($packageInfo['commands'])) {
                    $manifest[$package['name']]['commands'] = $packageInfo['commands'];
                }
            }
        }

        $this->write($manifest);
    }

    // TODO : améliorer le code en utilisant le fichier filesystem pour écrire le contenu du fichier + effectuer le test du répertoire "writable".
    private function write(array $manifest): void
    {
        if (! is_writable($this->runtimeDir)) {
            throw new RuntimeException('The ' . $this->runtimeDir . ' directory must be present and writable.');
        }

        // TODO : on devrait pas enregistrer le contenu du fichier dans un .json, ca serait plus simple à ecrire/lire ????
        // TODO : utilise $this->files->write pour écrire le fichier, non ?????
        file_put_contents($this->manifestPath, '<?php return ' . var_export($manifest, true) . ';');
    }

    public function getProviders(): array
    {
        return $this->getMeta('providers');
    }

    public function getAliases(): array
    {
        return $this->getMeta('aliases');
    }

    public function getBootloaders(): array
    {
        return $this->getMeta('bootloaders');
    }

    public function getCommands(): array
    {
        return $this->getMeta('commands');
    }

    private function getMeta(string $key): array
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

        // TODO : améliorer le code : faire directement un require, et utiliser la méthode $this->files->exists() plutot que la méthode file_exists(). Virer de la classe FileSystem la méthode getRequire qui ne sera plus utilisée.
        // TODO : on devrait pas enregistrer le contenu du fichier dans un .json, ca serait plus simple à ecrire/lire ????
        return $this->manifest = file_exists($this->manifestPath) ?
            $this->filesystem->getRequire($this->manifestPath) : [];
    }
}
