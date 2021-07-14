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
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;

//https://github.com/loophp/launcher/blob/master/src/Launcher.php

//https://stackoverflow.com/questions/29078380/how-do-i-run-a-symfony-console-command-after-composer-install
//https://github.com/sensiolabs/SensioDistributionBundle/blob/master/Composer/ScriptHandler.php

//https://github.com/pestphp/pest/blob/master/src/Console/Thanks.php
//https://github.com/pestphp/pest/blob/4a45a7cc6ba6866854174348410947ecfaf1d5e4/src/Laravel/Commands/PestInstallCommand.php#L57

//https://github.com/brefphp/bref/blob/dcf224abb57bbb757091c2b3a73132f149295d9d/src/Console/OpenUrl.php
//https://github.com/4shen/webshell/blob/5aa1fc5cfe6d51129a93ce5be194619de99cd96f/dataset/benign/netz98/n98-magerun-develop/src/N98/Magento/Command/OpenBrowserCommand.php#L61

//https://github.com/symfony/thanks/blob/main/src/Command/FundCommand.php
//https://github.com/symfony/thanks/blob/main/src/Command/ThanksCommand.php
//https://github.com/symfony/thanks/blob/e9c4709560296acbd4fe9e12b8d57a925aa7eae8/src/Thanks.php#L75
//https://github.com/symfony/flex/blob/main/src/Flex.php#L475

//https://github.com/humanmade/altis-local-chassis/blob/master/inc/class-command.php#L144
//https://github.com/humanmade/altis-local-chassis/blob/master/inc/class-command.php#L179

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

        // If there is no interaction possible, it's useless to ask a question!
        //if ($this->option('no-interaction')) {
        if (! $this->input->isInteractive()) {
            return self::SUCCESS;
        }

        $this->newLine();

        // TODO : utiliser la méthode native ask() :
        //https://github.com/illuminate/console/blob/master/Concerns/InteractsWithIO.php#L143
        //https://github.com/symfony/console/blob/5.3/Style/SymfonyStyle.php#L267
        // TODO : réfléchir pourquoi on utilise un new ArrayInput au lieu d'utiliser le $this->input
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

/*
    private function systemFallBack($url): Process
    {
        switch (PHP_OS_FAMILY) {
            case 'Darwin':
                return new Process(['open', $url]);
            case 'Windows':
                return new Process(['cmd', '/c', 'start', $url]);
            default:
                return new Process(['xdg-open', $url]);
        }
    }
    */
}
