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
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Event\ListenerData;
use ReflectionObject;

// TODO : exemple avec un filtre sur les noms => https://github.com/symfony/framework-bundle/blob/4a8f8840cc4c162cf1d31b22902db4c169b87517/Command/DebugAutowiringCommand.php

// TODO : améliorer le filtrage des évents via l'option --events     https://symfony.com/doc/current/event_dispatcher.html#debugging-event-listeners

// TODO : https://github.com/symfony/symfony/blob/e34cd7dd2c6d0b30d24cad443b8f964daa841d71/src/Symfony/Bundle/FrameworkBundle/Command/EventDispatcherDebugCommand.php

//https://github.com/hyperf/devtool/blob/master/src/Describe/ListenersCommand.php
//https://github.com/hyperf/hyperf/blob/2aa967ed6b0f55c4f8a09e0e69a85d5a4bf72f27/src/devtool/src/Describe/ListenersCommand.php

// TODO : afficher aussi la priorité de chaque event, éventuellement faire un sort sur la priorité pour afficher le détail des listeners à executer dans l'ordre des priorité, cela pourra aider lors du debug !!!!

/**
 * A console command to display application events.
 */
final class EventListCommand extends AbstractCommand
{
    protected static $defaultName = 'event:list';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('List application events and associated listeners.')
            ->addOption('events', 'e', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified information by events.', null);
    }

    public function perform(ListenerProviderInterface $provider): int
    {
        // TODO : améliorer ce code je pense qu'un truc comme cela est possible (cad que si on trouve une valeur on la parse sinon ca retournera la valeur par défaut qui est null !!!!) :
        /*
            if($events = $this->input->getOption('events')) {
                $events = explode(',', $events);
            }
            if($listeners = $this->input->getOption('listeners')) {
                $listeners = explode(',', $listeners);
            }
        */

        // TODO : voir si il est pas plus simple de mettre dans la classe AbstractCommand une finction getOptions(string $name, string $separator= ',') qui se charge de faire le explode + un trim sur les espace pour récupérer les multiples valeurs de l'option saisie par l'utilisateur !!!!
        $events = $this->input->getOption('events');
        $events = $events ? explode(',', $events) : null; // TODO : faire un trim pour les espaces ici car ca évitera de la faire plus tard dans la méthode isMatch() !!!

        $data = $this->handleData($provider, $events); // TODO : renommer la variable $data en $description

        if ($data !== []) {
            $this->show($data, $this->output); // TODO : virer le 2eme paramétre $ouput qui ne sert à rien, accéder directement au $this->output dans la méthode show() !!!
        } else {
            $this->notice('No events found in the dispatcher.');
        }

        return self::SUCCESS;
    }

    private function handleData(ListenerProviderInterface $provider, ?array $events): array
    {
        $data = [];
        $allListeners = $this->getValue($provider, 'listeners'); // TODO : nom de la variable à améliorer !!!

        foreach ($allListeners as $event => $listeners) {
            // Filter on the event name if '-events' is specified.
            if ($events && ! $this->isMatch($event, $events)) {
                continue;
            }

            $data[$event] = $this->describesCallables($listeners); // TODO : utiliser la méthode Support\Callback::toString pour avoir la description du callable !!!
        }

        return $data;
    }

    /**
     * Describes the listerners callables as string representation.
     *
     * @param array<callable> $callables The array of listeners callable.
     *
     * @return array<string> The array with the callable string description.
     */
    // TODO : code à améliorer !!! et à déplacer dans une classe de type VarDumper ???
    // TODO : fonction à renommer en exportCallables ????
    // TODO : utiliser la méthode Support\Callback::toString pour avoir la description du callable !!!
    private function describesCallables(array $callables): array
    {
        $data = [];
        foreach ($callables as $callable) {
            // TODO : utiliser plutot un getType et un switch !!!
            if (is_string($callable)) {
                $data[] = $callable;
            }
            if (is_object($callable)) {
                if($callable instanceof \Closure) {
                    $data[] = 'Closure()'; // TODO : éventuellement afficher le fichier+ligne de la closure en utilisant une reflection !!! regarder la méthode getClosureSignature ici : https://github.com/yiisoft/injector/blob/master/src/ArgumentException.php#L40
                    // TODO : eventuellement faire un unwrap de la closure pour retrouver la méthode source lorsqu'on a fait un Closure::fromCallable => https://github.com/nette/utils/blob/master/src/Utils/Callback.php#L120
                } else {
                    $data[] = get_class($callable);
                }
            }
            if (is_array($callable)) {
                $class = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
                $data[] = $class . '::' . $callable[1];
            }
        }

        return $data;
    }

    // TODO : on doit surement pouvoir remplacer cette méthode par un truc du genre Arr::containsPartial ou un truc du genre !!!! car ca ressemble à un in_array($keywords, $target)
    // TODO : renommer en méthode en filterEvents car avant elle était utilisée pour plusieurs type de match, mais maintenant on ne l'utilise que pour matcher des events !!!
    private function isMatch(string $target, array $keywords = [])
    {
        foreach ($keywords as $keyword) {
            // TODO : on doit surement pourvoir utiliser la méthode Str::contains($strictCase = false)
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
        // Drop the last TableSeparator presents in the array.
        array_pop($rows);

        $table = $this->table(['Event(s)', 'Listener(s)'], $rows);
        $table->render();
    }

    /**
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    //https://github.com/nette/web-addons.nette.org/blob/e985a240f30d2d4314f97cb2fa9699476d0c0a68/tests/libs/Access/Property.php
    private function getValue(object $object, string $property)
    {
        $r = new ReflectionObject($object);
        $prop = $r->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}
