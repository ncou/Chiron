<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Chiron\ErrorHandler\Formatter\FormatterInterface;
use Chiron\ErrorHandler\Formatter\PlainTextFormatter;
use Chiron\Http\Exception\HttpException;
//use Chiron\Http\Psr\Response;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

final class ConsoleRenderer
{
    private $output;
    private $highlighter;

    public function __construct()
    {
        $this->highlighter = new Highlighter();
    }

    public function render(Throwable $exception): void
    {
        $this->output = new ConsoleOutput(); //new StreamOutput(fopen('php://stderr', 'w'));

        $this->renderTitleAndDescription2($exception);

        $frames = $exception->getTrace();

        array_unshift($frames, [
                    'function' => '',
                    'file' => $exception->getFile() ?: 'n/a',
                    'line' => $exception->getLine() ?: 'n/a',
                    'args' => [],
                ]);

        $editorFrame = array_shift($frames);

        $this->renderEditor($editorFrame);

        $this->renderTrace($frames);
    }









    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     */
    protected function renderEditor(array $frame): self
    {
        $file = $this->getFileRelativePath($frame['file']);

        // getLine() might return null so cast to int to get 0 instead
        $line = (int) $frame['line'];
        $this->renderInternal('in <fg=green>' . $file . '</>' . ':<fg=green>' . $line . '</>');

        // TODO : vérifier que le $frame['file'] est bien un is_file()
        $content = $this->highlighter->highlight(file_get_contents($frame['file']), $line);
        //$content = (new Highlighter2())->highlightLines(file_get_contents($frame['file']), $line);


        $this->output->writeln($content);

        return $this;
    }

    /**
     * Renders the trace of the exception.
     */
    protected function renderTrace(array $frames): self
    {
        foreach ($frames as $i => $frame) {
            $file     = $this->getFileRelativePath($frame['file'] ?? 'n/a');
            $line     = $frame['line'] ?? 'n/a';
            $class    = empty($frame['class']) ? '' : $frame['class'] . '::';
            $function = $frame['function'];
            //$args     = $frame['args']; //$this->argumentFormatter->format($frame->getArgs());
            $pos      = str_pad((string) ((int) $i + 1), 4, ' ');

            $this->renderInternal("<fg=yellow>$pos</><fg=default;options=bold>$file</>:<fg=default;options=bold>$line</>");
            //$this->renderInternal("<fg=white>    $class$function($args)</>", false);
            $this->renderInternal("<fg=white>    $class$function()</>", false);
        }

        return $this;
    }

    /**
     * Renders the title of the exception.
     */
    protected function renderTitleAndDescription(Throwable $exception): self
    {
        $message   = rtrim($exception->getMessage());
        $class     = get_class($exception);//$inspector->getExceptionName();

        if ($exception instanceof \Error) {
            $this->renderInternal("<bg=magenta;options=bold> $class </>");
        } else {
            $this->renderInternal("<bg=red;options=bold> $class </>");
        }
        $this->output->writeln('');

        $this->output->writeln("<fg=default;options=bold>  $message</>");

        return $this;
    }

    protected function renderTitleAndDescription2(Throwable $exception): self
    {
        $message   = rtrim($exception->getMessage());
        $class     = get_class($exception);//$inspector->getExceptionName();

        $lines = preg_split('/\r?\n/', $message);



        if ($exception instanceof \Error) {
            $this->renderInternal("<bg=magenta;options=bold> $class </>");
        } else {
            $this->renderInternal("<bg=red;options=bold> $class </>");
        }
        $this->output->writeln('');

        //$this->output->writeln("<fg=default;options=bold>  $message</>");

        foreach ($lines as $line) {
            //$messages[] = sprintf('<error>  %s  %s</error>', OutputFormatter::escape($line[0]), str_repeat(' ', $len - $line[1]));
            $this->output->writeln("<fg=default;options=bold>  $line</>");
        }

        return $this;
    }

    /**
     * Renders an message into the console.
     *
     * @return $this
     */
    protected function renderInternal(string $message, bool $break = true): self
    {
        if ($break) {
            $this->output->writeln('');
        }

        $this->output->writeln("  $message");

        return $this;
    }

    /**
     * Returns the relative path of the given file path.
     */
    protected function getFileRelativePath(string $filePath): string
    {
        $cwd = (string) getcwd();

        if (!empty($cwd)) {
            // TODO : utiliser un directory_separator plutot que "/"
            return str_replace("$cwd/", '', $filePath);
        }

        return $filePath;
    }















    public function render_2(Throwable $exception): void
    {
        $output = new StreamOutput(fopen('php://stderr', 'w'));
        $this->setFormaters($output);


        $formatter = new OutputFormatter();

        //$header = "  {$exception->getOriginalClass()} (code {$exception->getCode()})";
        $header = '  ' . get_class($exception);
        if ('' !== $msg = $exception->getMessage()) {
            $header .= ' ' . $msg;
        }
        $header .= '  ';
        $emptyLine = \str_pad('', \mb_strlen($header), ' ');
        $output->writeln("<header>{$emptyLine}</header>");
        $output->writeln("<header>{$formatter->escape($header)}</header>");
        $output->writeln("<header>{$emptyLine}</header>");
        //$output->writeln('');

        //$output->writeln((string)$exception->getStackTrace());

/*
        $stepNo = 0;
        foreach ($exception->getStackTrace() as $step) {
            $output->writeln('');
            $this->renderStep($step, $output, $formatter);
            $stepNo++;
            if ($stepNo === $this->stepLimit) {
                break;
            }
        }*/
    }

    private function setFormaters(OutputInterface $output)
    {
        $styles = array(
            'header' => new OutputFormatterStyle('white', 'red', array('bold')),
            'hcode'  => new OutputFormatterStyle('green', 'black'),
            'code'   => new OutputFormatterStyle(),
            'ecode'  => new OutputFormatterStyle('white', 'red'),
        );
        $formater = $output->getFormatter();
        foreach ($styles as $name => $style) {
            if (!$formater->hasStyle($name)) {
                $formater->setStyle($name, $style);
            }
        }
    }


}

