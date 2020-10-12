<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Console\AbstractCommand;
use Chiron\Config\SecurityConfig;
use Chiron\Filesystem\Filesystem;
use Chiron\Core\Helper\Random;
use Symfony\Component\Console\Input\InputOption;

// TODO : faire passer les classes de type command en "final" + virer les protected non nécessaires
// TODO : passer les méthodes "perform" en protected pour chaque classe de type "Command"
// TODO : renommer la classe en SecurityKeyCommand et le $defaultName = 'security:key'
final class EncryptKeyCommand extends AbstractCommand
{
    protected static $defaultName = 'encrypt:key';

    protected function configure()
    {
        $this
            ->setDescription('Generate new security key.')
            ->addOption('mount', 'm', InputOption::VALUE_OPTIONAL, 'Mount security key into given file');
    }

    public function perform(Filesystem $filesystem, SecurityConfig $config): int
    {
        $key = Random::generateId();

        $this->sprintf("<info>New security key:</info> <fg=cyan>%s</fg=cyan>\n", $key);

        $filepath = $this->option('mount');
        if ($filepath === null) {
            // Only show the generated key, if the optional "mount" file path is not defined.
            return self::SUCCESS;
        }

        if ($filesystem->missing($filepath)) {
            $this->sprintf('<error>Unable to find `%s`</error>', $filepath);

            return self::FAILURE;
        }

        $content = $filesystem->read($filepath);
        // TODO : utiliser le 4eme argument pour savoir si le str_replace a fonctionné et seulement dans ce cas là on peut afficher un "success", si ce n'est pas le cas il faut lever une "notice"/"warning" pour indiquer qu'il n'y a pas eu de remplacements !!!!
        $content = str_replace($config->getKey(), $key, $content);

        $filesystem->write($filepath, $content);

        $this->writeln('<comment>Security key has been updated.</comment>');

        return self::SUCCESS;
    }
}
