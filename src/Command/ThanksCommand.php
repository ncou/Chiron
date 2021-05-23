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
use Chiron\Framework;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;

//https://github.com/pestphp/pest/blob/master/src/Console/Thanks.php
//https://github.com/pestphp/pest/blob/4a45a7cc6ba6866854174348410947ecfaf1d5e4/src/Laravel/Commands/PestInstallCommand.php#L57

//https://github.com/brefphp/bref/blob/dcf224abb57bbb757091c2b3a73132f149295d9d/src/Console/OpenUrl.php
//https://github.com/4shen/webshell/blob/5aa1fc5cfe6d51129a93ce5be194619de99cd96f/dataset/benign/netz98/n98-magerun-develop/src/N98/Magento/Command/OpenBrowserCommand.php#L61

//https://github.com/symfony/thanks/blob/main/src/Command/FundCommand.php
//https://github.com/symfony/thanks/blob/main/src/Command/ThanksCommand.php

/**
 * A console command to display information about the current installation.
 */
// TODO : passer les méthodes "perform" en protected pour chaque classe de type "Command"
final class ThanksCommand extends AbstractCommand
{
    protected static $defaultName = 'thanks';

    /** @var array<int, string> */
    private const FUNDING_MESSAGES = [
        '  - Star or contribute to Chiron:',
        '    <options=bold>https://github.com/chironphp/chiron</>',
        '  - Tweet something about Chiron on Twitter:',
        '    <options=bold>https://twitter.com/chironphp</>',
        '  - Sponsor the creator:',
        '    <options=bold>https://github.com/sponsors/ncou</>'
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Open the github url to allow the user to star the projet');
    }

    public function perform(): int
    {
        foreach (self::FUNDING_MESSAGES as $message) {
            $this->output->writeln($message);
        }

        // If there is no interaction possible, it's useless to ask an interactive question!
        if ($this->option('no-interaction')) {
            return self::SUCCESS;
        }

        $this->newLine();

        $wantsToSupport = (new SymfonyQuestionHelper())->ask(
            new ArrayInput([]),
            $this->output,
            new ConfirmationQuestion(
                'Can you quickly <options=bold>star our GitHub repository</>?',
                true
            )
        );

        if ($wantsToSupport === true) {
            // MacOS
            if (PHP_OS_FAMILY == 'Darwin') {
                exec('open https://github.com/chironphp/chiron');
            }
            if (PHP_OS_FAMILY == 'Windows') {
                exec('start https://github.com/chironphp/chiron');
            }
            if (PHP_OS_FAMILY == 'Linux') {
                exec('xdg-open https://github.com/chironphp/chiron');
            }
        }

        return self::SUCCESS;
    }
}
