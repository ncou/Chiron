<?php

declare(strict_types=1);

namespace Chiron\Service;

use Chiron\Container\Container;
use Chiron\Core\Container\Bootloader\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Core\Provider\CoreServiceProvider;
use Chiron\Event\EventDispatcher;
use Chiron\Event\ListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Manages Services living inside the Container.
 */
// TODO : renommer en ServicesManager au pluriel ???
// TODO : gérer les doublons lors de l'jout d'un provider ou bootloader, cad stocker dans un tableau (le nom de classe) ce qui est déjà traité.
// TODO : utiliser le containerawaretrait ????
// TODO : il faudrait que cette classe fasse un extends de la classe "Container" comme ca on pourrait utiliser aussi les autres méthodes style mutation/bind/make/etc...
// TODO : ajouter une méthode public getContainer() ou alors passer la variable de classe $container en public pour qu'on puisse manipuler directement el container ???? ou alors créer d'office des méthode proxy du style bind/muntation/make...etc ou alors en plan B il faudrait faire un extends de la classe container !!!

// TODO : il faudrait plutot faire un extend de la classe Chiron\Container ???? ca sera plus simple car on va devoir manipuler aussi les méthodes bind et singleton et make !!!!

// TODO : il faudrait surement que cette classe implement SingletonInterface !!!! ex : dans le cas ou on appel la classe ServiceManager dans un bootloader pour injecter un provider ou un bootloader. Par exemple dans le classe ApplicationBootloader on pourrait trés bien manipuler une classe ServiceManager::class au lieu de Application::class lorsqu'on va injecter les services depuis le fichier app.php dans l'application !!!
final class ServiceManager
{
    /** @var Container */
    public $container;

    /** @var BootloaderInterface[] */
    private $bootloaders = [];

    /**
     * Indicates if the botloaders stack has been "booted".
     *
     * @var bool
     */
    private $booted = false;

    public function __construct()
    {
        $this->container = new Container();

        // Bind this ServiceManager instance as singleton in the container.
        $this->container->singleton(self::class, $this);
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProviderInterface|string $provider
     */
    // TODO : améliorer le code : https://github.com/laravel/framework/blob/8.x/src/Illuminate/Foundation/Application.php#L659
    // TODO : permettre à l'utilisateur de passe un tableau de string ou de ServiceProviderInterface. et appeller cette nouvelle méthode addProviders()
    public function addProvider($provider): void
    {
        if (is_string($provider)) {
            $provider = $this->container->injector()->build($provider); // TODO : améliorer le code ? cad mettre dans une methode resolve, et eventuellement faire un try/catch pour convertir les exception en ServiceException !!!
        }

        // TODO : vérifier que c'est bien une instance ServiceProviderInterface::class sinon lever une exception !!!

        $provider->register($this->container);
    }

    /**
     * Bootload a service.
     *
     * @param BootloaderInterface|string $bootloader
     */
    // TODO : permettre à l'utilisateur de passe un tableau de string ou de BootloaderInterface. et appeller cette nouvelle méthode addBootloaders()
    public function addBootloader($bootloader): void
    {
        if (is_string($bootloader)) {
            $bootloader = $this->container->injector()->build($bootloader); // TODO : améliorer le code ? cad mettre dans une methode resolve, et eventuellement faire un try/catch pour convertir les exception en ServiceException !!!
        }

        // TODO : vérifier que c'est bien une instance BootloaderInterface::class sinon lever une exception !!!

        if ($this->booted) {
            $bootloader->bootload($this->container->injector()); // TODO : attention il faudrait pas gérer le cas ou il y a un doublon dans les bootloaders ajoutés ????
        } else {
            $this->bootloaders[] = $bootloader;
        }
    }

    /**
     * Boot the services bootloaders.
     */
    public function boot(): void
    {
        if (! $this->booted) {
            $this->booted = true;

            foreach ($this->bootloaders as $bootloader) {
                $bootloader->bootload($this->container->injector());
            }
        }
    }
}
