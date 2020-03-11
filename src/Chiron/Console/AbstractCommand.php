<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Invoker\Invoker;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use LogicException;

//https://github.com/spiral/console/blob/master/src/Command.php
//https://github.com/spiral/console/blob/master/src/Traits/HelpersTrait.php

//https://github.com/viserio/console/blob/master/Command/AbstractCommand.php
//https://github.com/leevels/console/blob/master/Command.php

//https://github.com/illuminate/console/blob/master/Command.php
//https://github.com/symfony/console/blob/master/Command/Command.php

//https://github.com/illuminate/console/blob/6.x/Concerns/InteractsWithIO.php

/**
 * Provides automatic command configuration and access to global container scope.
 */
// TODO : ajouter le containerAwareTrait + ContainerAwareInterface !!!!
// TODO : il faudrait pas ajouter une ligne du style "abstract function perform(): int" ????
abstract class AbstractCommand extends SymfonyCommand
{
    /**
     * The console command input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console command output.
     *
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $output;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Get the output implementation.
     *
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     *
     * @codeCoverageIgnore
     */
    // TODO : méthode à renommer en "output()" ????
    // TODO : il faudrait pas que la valeur de retour soit directement un OutputInterface ????
    // TODO : vérifier l'utilité de cette méthode.
    public function getOutput(): SymfonyStyle
    {
        return $this->output;
    }

    /**
     * Store the input and output object, and 'Run' the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    // TODO : déplacer ce code dans la méthode execute !!!!
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        // TODO : créer une variable protected nommée "$this->io" qui contiendra le style, ne pas utiliser "$this->output"
        $this->output = new SymfonyStyle($input, $output);

        /*
        $this->output = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );*/

        return parent::run($input, $output);
    }

    /**
     * {@inheritdoc}
     *
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    // TODO : lever une logicexception si la méthode 'perform' n'est pas trouvée dans la classe mére ?
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->container === null) {
            throw new LogicException('Your forgot to call the setContainer function.');
        }

        $invoker = new Invoker($this->container);

        // TODO : ajouter un contrôle sur la valeur de retour pour s'assurer que c'est bien un int qui est renvoyé ??? ou alors retourner d'office le code 0 qui indique qu'il n'y a pas eu d'erreurs ????
        return (int) $invoker->call([$this, 'perform']);
    }

    /**
     * Write a string as standard output.
     *
     * @param string          $string
     * @param null|string     $style          The output style of the string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function line(string $string, ?string $style = null): void
    {
        $styledString = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->getOutput()->writeln($styledString);
    }

    public function confirm($question, $defaults = false): bool
    {
        return $this->output->confirm($question, $defaults);
    }

    /**
     * Configures the command.
     */
    /*
    protected function configure(): void
    {
        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }*/

    /**
     * Define command options.
     *
     * @return array
     */
    /*
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }*/

    /**
     * Define command arguments.
     *
     * @return array
     */
    /*
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }*/

    /**
     * Identical to write function but provides ability to format message. Does not add new line.
     *
     * @param string $format
     * @param array  ...$args
     */
    protected function sprintf(string $format, ...$args)
    {
        return $this->output->write(sprintf($format, ...$args), false);
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, bool $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     *
     * @param array  $headers
     * @param array  $rows
     * @param string $style
     * @return Table
     */
    protected function table(array $headers, array $rows = [], string $style = 'default'): Table
    {
        $table = new Table($this->output);

        return $table->setHeaders($headers)->setRows($rows)->setStyle($style);
    }

    /**
     * Determine if the given argument is present.
     *
     * @param  string|int  $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string|null  $key
     * @return string|array|null
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string|null  $key
     * @return string|array|bool|null
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }

}
