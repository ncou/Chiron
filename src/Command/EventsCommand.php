<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Application;
use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Filesystem\Path;
use Chiron\Service\Bootloader\EnvironmentBootloader;
use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Chiron\Framework;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Event\ListenerData;

//https://github.com/hyperf/hyperf/blob/2aa967ed6b0f55c4f8a09e0e69a85d5a4bf72f27/src/devtool/src/Describe/ListenersCommand.php

// TODO : afficher aussi la priorité de chaque event, éventuellement faire un sort sur la priorité pour afficher le détail des listeners à executer dans l'ordre des priorité, cela pourra aider lors du debug !!!!

/**
 * A console command to display application events.
 */
final class EventsCommand extends AbstractCommand
{
    protected static $defaultName = 'events';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Displays the events and associated listeners.')
            ->addOption('events', 'e', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by events.', null)
            ->addOption('listeners', 'l', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by listeners.', null);
    }

    public function perform(ListenerProviderInterface $provider): int
    {
        $events = $this->input->getOption('events');
        $events = $events ? explode(',', $events) : null;
        $listeners = $this->input->getOption('listeners');
        $listeners = $listeners ? explode(',', $listeners) : null;

        $data = $this->handleData($provider, $events, $listeners);

        if ($data !== []) {
            $this->show($data, $this->output);
        } else {
            $this->notice('No events found in the dispatcher.');
        }

        return self::SUCCESS;
    }

    private function handleData(ListenerProviderInterface $provider, ?array $events, ?array $listeners): array
    {
        $data = [];
        if (! property_exists($provider, 'listeners')) {
            return $data;
        }

        foreach ($provider->listeners as $listener) {
            if ($listener instanceof ListenerData) {
                $event = $listener->event;
                [$object, $method] = $listener->listener;
                $listenerClassName = get_class($object);
                if ($events && ! $this->isMatch($event, $events)) {
                    continue;
                }
                if ($listeners && ! $this->isMatch($listenerClassName, $listeners)) {
                    continue;
                }

                $listenerCallable = implode('::', [$listenerClassName, $method]);
                $data[$event][] = $listenerCallable;
            }
        }
        return $data;
    }

    private function isMatch(string $target, array $keywords = [])
    {
        foreach ($keywords as $keyword) {
            // TODO : on doit surement pourvoir utiliser la méthode Str::contains()
            if (stripos($target, trim($keyword)) !== false) {
                return true;
            }
        }
        return false;
    }

    private function show(array $data, OutputInterface $output)
    {
        $rows = [];
        foreach ($data as $event => $listeners) {
            $name = $event;
            foreach ($listeners as $listener) {
                $rows[] = [$name, $listener];
                // only display one time the event name in case there is multiple listeners.
                $name = '';
            }

            $rows[] = new TableSeparator();
        }
        // Skip the last TableSeparator presents in the array.
        $rows = array_slice($rows, 0, count($rows) - 1); // TODO : utiliser une classe Arr dans le package chiron/support pour enlever le dernier élement du tableau (style une fonction pop())

        $table = $this->table(['Event(s)', 'Listener(s)'], $rows);
        $table->render();
    }
}
