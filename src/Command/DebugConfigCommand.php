<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Application;
use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Filesystem\Path;
use Chiron\Bootloader\EnvironmentBootloader;
use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Chiron\Core\Configure;
use Symfony\Component\Yaml\Yaml;
use Chiron\Support\Dump;
use Chiron\Support\VarDumper;
use Chiron\Support\VarDumper2;

//https://github.com/symfony/framework-bundle/blob/4e3b7215071f02e930b00f69741dfd4dab3c31e7/Command/ConfigDebugCommand.php

/**
 * A console command to display config files (usefull for debug purpose).
 */
final class DebugConfigCommand extends AbstractCommand
{
    protected static $defaultName = 'debug:config';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Dumps the current configuration for a service.')
            ->addArgument('name', InputArgument::OPTIONAL, 'The service name');
    }

    public function perform(Configure $configure): int
    {
        $input = $this->input;
        $output = $this->output;


        $io = new SymfonyStyle($input, $output);
        $errorIo = $io->getErrorStyle();

        if (null === $name = $input->getArgument('name')) {
            /*
            $this->listBundles($errorIo);

            $kernel = $this->getApplication()->getKernel();
            if ($kernel instanceof ExtensionInterface
                && ($kernel instanceof ConfigurationInterface || $kernel instanceof ConfigurationExtensionInterface)
                && $kernel->getAlias()
            ) {
                $errorIo->table(['Kernel Extension'], [[$kernel->getAlias()]]);
            }*/

            $errorIo->comment('Provide the name of a bundle as the first argument of this command to dump its configuration. (e.g. <comment>debug:config FrameworkBundle</comment>)');
            $errorIo->comment('For dumping a specific option, add its path as the second argument of this command. (e.g. <comment>debug:config FrameworkBundle serializer</comment> to dump the <comment>framework.serializer</comment> configuration)');

            return self::SUCCESS;
        }

/*
        $extension = $this->findExtension($name);
        $container = $this->compileContainer();

        $extensionAlias = $extension->getAlias();
        $extensionConfig = [];
        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ValidateEnvPlaceholdersPass) {
                $extensionConfig = $pass->getExtensionConfig();
                break;
            }
        }

        if (!isset($extensionConfig[$extensionAlias])) {
            throw new \LogicException(sprintf('The extension with alias "%s" does not have configuration.', $extensionAlias));
        }

        $config = $container->resolveEnvPlaceholders($extensionConfig[$extensionAlias]);
*/

        if ($configure->hasConfig($name)) {
            $config = $configure->getConfigData($name);
        } else {
            $config = ['not found']; // TODO : code à améliorer il faudra surement afficher un message d'erreur dans la console.
        }


        $io->title(
            sprintf('Current configuration for "%s"', $name)
        );

        //$io->writeln(print_r($config)); // TODO : utiliser un Yaml::dump() pour afficher de maniére plus "friendly" le contenu de l'array.
        //$io->writeln(Yaml::dump([$name => $config], 10)); // TODO : attention il n'y a pas la dépendance sur le package yaml dans le framework chiron/chiron donc ca risque de ne pas foncionner !!!!

        //$dump = new Dump();
        //$io->writeln($dump->variable([$name => $config]));

        //$io->writeln(VarDumper::dumpAsString($config));
        //$io->writeln(VarDumper::export($config));

        //$io->writeln(VarDumper2::export($config));
        //$io->writeln(VarDumper2::export([$name => $config])); // TODO : ajouter dans cette classe de Command directement la méthode export() pour évider d'avoir à utiliser la classe VarDumper2 !!!

        $io->writeln($this->dumpConfig([$name => $config]));

        return self::SUCCESS;
    }

    /**
     * Exports a variable as a string representation.
     *
     * @param mixed $var the variable to be exported.
     *
     * @return string a string representation of the variable
     */
    private function dumpConfig($var): string
    {
        $result = '';
        $this->dump($var, 0, $result);

        return $result;
    }

    /**
     * Dump a pretty export of the given variable.
     *
     * @param mixed $var   variable to be exported
     * @param int   $level depth level
     */
    // TODO : améliorer le code avec cette classe :
    // https://github.com/hechoendrupal/drupal/blob/eeff134d76964e4c4612398955077559e2740fe1/lib/Drupal/Component/Utility/Variable.php
    // https://github.com/drupal/core-utility/blob/8.8.x/Variable.php
    private function dump($var, int $level, string &$result): void
    {
        switch (gettype($var)) {
            case 'NULL':
                $result .= 'null';

                break;
            case 'string':
                if (strtolower($var) === 'true'
                    || strtolower($var) === 'false'
                    || strtolower($var) === 'null'
                    || trim($var) === ''
                    || ctype_digit($var)
                ) {
                    $var = "'" . $var . "'";
                }

                if (self::isBinaryString($var)) {
                    $var = '{binary} ' . base64_encode($var);
                }

                $result .= $var;

                break;
            case 'array':
                if (empty($var)) {
                    $result .= '[ ]';
                } else {
                    $keys = array_keys($var);
                    $outputKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    foreach ($keys as $key) {
                        $result .= "\n" . $spaces;
                        if ($outputKeys) {
                            $this->dump($key, 0, $result);
                            $result .= ': ';
                        } else {
                            $result .= '- ';
                        }
                        $this->dump($var[$key], $level + 1, $result);
                    }
                }

                break;
            case 'resource':
                $result .= '{resource}';

                break;
            case 'object':
                $result .= '{object} ' . get_class($var);

                break;
            default:
                $result .= var_export($var, true);
        }
    }

    // TODO : à déplacer dans la classe Str::class pour faire partie des différents helpers !!!!
    private static function isBinaryString(string $value): bool
    {
        return !preg_match('//u', $value) || preg_match('/[^\x00\x07-\x0d\x1B\x20-\xff]/', $value);
    }
}
