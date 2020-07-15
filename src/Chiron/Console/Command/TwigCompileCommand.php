<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Console\AbstractCommand;
use Chiron\Console\ExitCode;
use Chiron\PublishableCollection;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Views\Config\TwigConfig;

use InvalidArgumentException;
use RuntimeException;

use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use Chiron\Views\TemplateRendererInterface;

//https://github.com/symfony/twig-bridge/blob/17cbe5aa0a503c67d76dd6248ab8a3a856cf7105/Command/LintCommand.php
//https://github.com/symfony/symfony/blob/9a4a96910d02275cc3a7912def65a6e39fec542d/src/Symfony/Bridge/Twig/Command/LintCommand.php

//https://github.com/narrowspark/framework/blob/3d39c891d93c0bc5b7f0148421abbf7143cd1813/src/Viserio/Bridge/Twig/Command/LintCommand.php
//https://github.com/narrowspark/framework/blob/81f39d7371715ee20aa888a8934c36c536e3d69e/src/Viserio/Provider/Twig/Command/LintCommand.php

//https://github.com/narrowspark/framework/blob/2a3536b821e685a3c7aa09f9a9b6eec9d873004f/src/Viserio/Bridge/Twig/Tests/Command/LintCommandTest.php
//https://github.com/narrowspark/framework/blob/81f39d7371715ee20aa888a8934c36c536e3d69e/src/Viserio/Provider/Twig/Tests/Command/LintCommandTest.php
final class TwigCompileCommand extends AbstractCommand
{
    /** @var Environment */
    // TODO : à renommer en "twig" !!!
    private $environment;

    protected static $defaultName = 'twig:compile';

    protected function configure(): void
    {
        $this->setDescription('Check compilation errors in the Twig templates files.');
    }

    public function perform(Filesystem $filesystem, TemplateRendererInterface $renderer): int
    {
        // (array) $this->option('files'), (array) $this->option('directories')

        $this->environment = $renderer->twig();

        $files = [];
        $extension = '*.'. $renderer->getExtension();

        // TODO : améliorer le code !!!! faire un iteratoraggregate ????
        foreach ($renderer->getPaths() as $path) {
            $result = $filesystem->find((string) $path, $extension);
            $files = array_merge($files, iterator_to_array($result));
        }

        // If no files are found.
        if (count($files) === 0) {
            // TODO : faire plutot un $this->error('no files found !');
            throw new RuntimeException('No twig files found.');
        }

        // TODO : améliorer le code c'est un patch temporaire !!!!
        //$showDeprecations = (bool) $this->option('show-deprecations');
        $showDeprecations = false;

        if ($showDeprecations) {
            $prevErrorHandler = \set_error_handler(static function (int $level, string $message, string $file, int $line) use (&$prevErrorHandler) {
                if ($level === \E_USER_DEPRECATED) {
                    $templateLine = 0;

                    if (\preg_match('/ at line (\d+) /', $message, $matches) === 1) {
                        $templateLine = $matches[1];
                    }

                    throw new Error($message, $templateLine);
                }

                return $prevErrorHandler ? $prevErrorHandler($level, $message, $file, $line) : false;
            });
        }

        $details = [];

        try {
            foreach ($files as $file) {
                // TODO : améliorer le code c'est un patch temporaire !!!!
                $file = $file->getRealPath();


                $details[] = $this->validate((string) file_get_contents($file), $file, $showDeprecations);
            }
        } finally {
            if ($showDeprecations) {
                restore_error_handler();
            }
        }

        // TODO : améliorer le code c'est un patch temporaire !!!!
        //return $this->display($details, $this->option('format'));

        return $this->display($details, 'txt');
    }


    /**
     * Validate the template.
     *
     * @param string $template         twig template
     * @param string $file
     * @param bool   $showDeprecations
     *
     * @return array
     */
    private function validate(string $template, string $file, $showDeprecations): array
    {
        $realLoader = $this->environment->getLoader();

        try {
            $temporaryLoader = new ArrayLoader([$file => $template]);

            $this->environment->setLoader($temporaryLoader);

            // TODO : il faudra faire un test avec un lexer pour changer la compilation.
            // TODO : utiliser directement la méthode ->compileSource(new Source(xxxx)); et faire un try/catch uniquement sur : use Twig\Error\SyntaxError;
            $nodeTree = $this->environment->parse($this->environment->tokenize(new Source($template, $file)));
            $code = $this->environment->compile($nodeTree);

            if ($showDeprecations) {
                $this->environment->display($code);
            }

            $this->environment->setLoader($realLoader);
        } catch (Error $exception) {
            $this->environment->setLoader($realLoader);

            // TODO : ajouter la ligne de l'erreur : https://github.com/symfony/twig-bridge/blob/17cbe5aa0a503c67d76dd6248ab8a3a856cf7105/Command/LintCommand.php#L163

            return [
                'template' => $template,
                'file' => $file,
                'valid' => false,
                'exception' => $exception,
            ];
        }

        return [
            'template' => $template,
            'file' => $file,
            'valid' => true,
        ];
    }

    /**
     * Output the results of the linting.
     *
     * @param array  $details validation results from all linted files
     * @param string $format  Format to output the results in. Supports txt or json.
     *
     * @throws InvalidArgumentException thrown for an unknown format
     *
     * @return int
     */
    private function display(array $details, string $format = 'txt'): int
    {
        $verbose = $this->isVerbose();

        switch ($format) {
            case 'txt':
                return $this->displayText($details, $verbose);
            case 'json':
                return $this->displayJson($details);

            default:
                throw new InvalidArgumentException(\sprintf('The format [%s] is not supported.', $format));
        }
    }

    /**
     * Output the results as text.
     *
     * @param array $details validation results from all linted files
     * @param bool  $verbose
     *
     * @return int
     */
    private function displayText(array $details, bool $verbose = false): int
    {
        $errors = 0;

        foreach ($details as $info) {
            if ($verbose && $info['valid']) {
                $file = ' in ' . $info['file'];

                $this->info('OK' . $file);
            } elseif (! $info['valid']) {
                $errors++;

                $this->renderException($info);
            }
        }

        $countDetails = \count($details);

        if ($errors === 0) {
            $countFileText = $countDetails === 1 ? 'file' : 'files';

            $this->comment(\sprintf('%d Twig %s contain valid syntax.', $countDetails, $countFileText));
        } else {
            $countFileText = $countDetails - $errors === 1 ? 'file' : 'files';

            $this->warning(\sprintf('%d Twig %s have valid syntax and %d contain errors.', $countDetails - $errors, $countFileText, $errors));
        }

        return \min($errors, 1);
    }

    /**
     * Output the results as json.
     *
     * @param array $details validation results from all linted files
     *
     * @return int
     */
    private function displayJson(array $details): int
    {
        $errors = 0;

        \array_walk(
            $details,
            static function (array &$info) use (&$errors): void {
                $info['file'] = (string) $info['file'];

                unset($info['template']);

                if (! $info['valid']) {
                    $info['message'] = $info['exception']->getMessage();

                    unset($info['exception']);

                    $errors++;
                }
            }
        );

        $this->line((string) \json_encode($details, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

        return \min($errors, 1);
    }

    /**
     * Output the error to the console.
     *
     * @param array $info details for the file that failed to be linted
     *
     * @return void
     */
    private function renderException(array $info): void
    {
        $exception = $info['exception'];

        $line = $exception->getTemplateLine();
        $lines = $this->getContext($info['template'], $line);

        $this->line(\sprintf('<error>Fail</error> in %s (line %s)', $info['file'], $line));

        foreach ($lines as $no => $code) {
            $this->line(
                \sprintf(
                    '%s %-6s %s',
                    $no === $line ? '<error>>></error>' : '  ',
                    $no,
                    $code
                )
            );

            if ($no === $line) {
                $this->line(\sprintf('<error>>> %s</error> ', $exception->getRawMessage()));
            }
        }
    }


    /**
     * Grabs the surrounding lines around the exception.
     *
     * @param string $template contents of Twig template
     * @param int    $line     line where the exception occurred
     * @param int    $context  number of lines around the line where the exception occurred
     *
     * @return array
     */
    private function getContext(string $template, $line, int $context = 3): array
    {
        $lines = \explode("\n", $template);
        $position = \max(0, $line - $context);
        $max = \min(\count($lines), $line - 1 + $context);
        $result = [];

        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            $position++;
        }

        return $result;
    }


    /**
     * Undocumented function.
     *
     * @param string $dir
     * @param array  $foundFiles
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function findTwigFiles(string $dir, array &$foundFiles): void
    {
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        } catch (UnexpectedValueException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        foreach ($iterator as $file) {
            if (\pathinfo($file->getRealPath(), \PATHINFO_EXTENSION) === 'twig') {
                $foundFiles[] = $file;
            }
        }
    }


}
