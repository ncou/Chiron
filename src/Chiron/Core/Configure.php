<?php

declare(strict_types=1);

namespace Chiron\Core;

use Chiron\Config\Config;
use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigLoader;
use Chiron\Config\Exception\ConfigException;
use Chiron\Container\SingletonInterface;
use Chiron\Filesystem\Filesystem;

//https://github.com/cakephp/cakephp/blob/master/src/Core/Configure.php

//https://github.com/illuminate/config/blob/master/Repository.php
//https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
//https://github.com/zendframework/zend-config/blob/master/src/Config.php

//https://github.com/limingxinleo/x-phalcon-config-center/blob/master/src/Config/Center/Client.php

// TODO : on devrait pas créer une classe ConfigFactory qui se charge de créer les objets Config ??? https://github.com/zendframework/zend-config/blob/master/src/Factory.php
// TODO : renommer la classe en Configure::class et laisser les méthode load() qui chargera aussi bien un fichier qu'un répertoire. + has() et get() & getConfig() pour avoir un retour d'objet mais aussi de tableau data et la méthode add() qui permet de charger les données d'une config depuis un array.
// TODO : renommer en "Configure::class" + faire un helper dans les fonction de type config_item($item, $section) ou config($section)
// TODO : améliorer le code, surtout la méthode read/check/has/set/merge etc...
final class Configure implements SingletonInterface
{
    /** @var Config[] */
    private $sections = [];

    /** @var Filesystem */
    private $filesystem;

    /** @var ConfigLoader */
    private $loader;

    // TODO : déplacer la ConfigLoader au niveau du paramétre du constructeur, cela laisse la possibilité d'utiliser un objet shared et instancié dans le container si par exemple l'utilisateur a décidé d'ajoputer un loaders spécifique dans le configloader par exemple. Idem pour l'objet Filesystem ????
    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->loader = new ConfigLoader();
    }

    // TODO : à coder, cela doit permettre de lire une clé qui est la concaténation de la section + '.' + item à récupérer via un get. Eventuellement si la section n'existe pas faire un levenishtein pour proposer un nom de section qui se rapproche de ce que l'utilisateur a saisi. Exemple :  Configure::read('App.defaultLocale'));
    public function read(string $key)
    {
        // TODO
    }

    public function hasConfig(string $section): bool
    {
        return isset($this->sections[$section]);
    }

    public function getConfig(string $section, ?string $subset = null): ConfigInterface
    {
        if (! $this->hasConfig($section)) {
            throw new ConfigException(sprintf('Config section "%s" not found in the manager !', $section));
        }

        $config = $this->sections[$section];

        if ($subset !== null) {
            // TODO : attention il faudrait gérer le cas ou le subset n'existe pas !!!!
            $data = $config->get($subset);

            if (! is_array($data)) {
                // TODO : afficher le nom du subset recherché dans le message de l'exception. Ca sera plus simple pour débugger !!! Afficher le gettype() pour indiquer si c'est une chaine ou null par exemple.
                throw new ConfigException('Subset must be an array !');
            }

            $config = new Config($data);
        }

        return $config;
    }

    // TODO : à voir si on conserve cette méthode ou si on force l'utilisateur a faire un toArray une fois qu'il récupére l'objet Config...
    public function getConfigData(string $section, ?string $subset = null): array
    {
        $config = $this->getConfig($section, $subset);

        return $config->getData();
    }

    // TODO : code à améliorer !!!!
    // TODO : il faudrait créer une méthode loadFromFiles qui lirait un tableau de fichiers (un objet Transversable par exemple)
    public function loadFromFile(string $file): void
    {
        if (! $this->filesystem->isFile($file)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid file path');
        }

        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($file)) {
                $this->config->merge($loader->load($file));

                return;
            }
        }

        throw new ConfigException(sprintf('Cannot load "%s"', $path));
    }

    // TODO : éventuellement lui passer un paramétre $section pour le nom et ensuite le contenu $data
    // TODO : c'est plutot une fonction "add()" ou "set()" plutot que loadFromArray !!!!
    public function loadFromArray(array $data): void
    {
        // TODO : à implémenter !!!!
    }

    public function loadFromDirectory(string $directory): void
    {
        if (! $this->filesystem->isDirectory($directory)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid directory path');
        }

        $directory = realpath($directory);
        $files = $this->filesystem->files($directory);

        foreach ($files as $file) {
            $section = $this->generateSectionName($file, $directory);
            $data = $this->loader->load($file->getRealPath());

            $this->merge($section, $data);
        }
    }

    /**
     * Generate the section name (nesting path + file name using dot separator).
     *
     * @param \SplFileInfo $file
     * @param string       $path
     *
     * @return string
     */
    //https://github.com/limingxinleo/x-phalcon-config-center/blob/master/src/Config/Center/Client.php#L40
    private function generateSectionName(\SplFileInfo $file, string $path): string
    {
        $directory = $file->getPath();
        $extension = '.' . $file->getExtension();

        if ($nested = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested . $file->getBasename($extension);
    }

    /**
     * @param array $appender
     */
    // TODO : conserver cette méthode en public ??? éventuelleùment cela permettrait de charger la config depuis un array (ce qui viendrait compléter les possibilité de chargement en plus des méthodes loadFromDirectory et loadFromFile) éventuellement renommer cette méthode en loadFromArray($section, $data)
    // TODO : éventuellement renommer cette méthode en "add()"
    public function merge(string $section, array $appender): void
    {
        // if the section is already present, we merge both the datas.
        $origin = $this->hasConfig($section) ? $this->getConfigData($section) : [];
        $result = $this->recursiveMerge($origin, $appender);
        //$result = array_merge($origin, $appender);
        $this->sections[$section] = new Config($result);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    //https://github.com/yiisoft/yii2-framework/blob/ecae73e23abb524bb637c37c62e4db5495f5f4f2/helpers/BaseArrayHelper.php#L117
    //https://github.com/hiqdev/composer-config-plugin/blob/master/src/utils/Helper.php#L27
    private function recursiveMerge($origin, $appender)
    {
        if (is_array($origin)
            && array_values($origin) !== $origin
            && is_array($appender)
            && array_values($appender) !== $appender) {
            foreach ($appender as $key => $value) {
                if (isset($origin[$key])) {
                    $origin[$key] = $this->recursiveMerge($origin[$key], $value);
                } else {
                    $origin[$key] = $value;
                }
            }

            return $origin;
        }

        return $appender;
    }
}
