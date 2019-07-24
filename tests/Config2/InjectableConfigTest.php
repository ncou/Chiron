<?php

declare(strict_types=1);

namespace Chiron\Tests\Config;

use Chiron\Container\Container;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Routing\Route;
use Chiron\Routing\Router;
use Chiron\Routing\RouterInterface;
use Chiron\Routing\RouteCollectorInterface;
use Chiron\Routing\Strategy\StrategyInterface;
use PHPUnit\Framework\TestCase;
use Chiron\Config\InjectableInterface;
use Chiron\Config\ConfigInflector;
use Chiron\Config\ConfigManager;
use Chiron\Config\AbstractInjectableConfig;

class InjectableConfigTest extends TestCase
{
    /**
     * Asserts that appropriately configured regex strings are added to patternMatchers.
     */
    public function testInjectableConfig()
    {
        $container = new Container();

        $container->share(ConfigManager::class);

        $container->inflector(InjectableInterface::class, new ConfigInflector($container));

        $htmlConfig = $container->get(HtmlConfig::class);

        echo var_dump($htmlConfig->toArray());

    }
}


class HtmlConfig extends AbstractInjectableConfig
{
    protected $config = ["https" => true];

    public function __construct(array $config = [])
    {

    }

    public function getLinkedFile(): string
    {
        return 'html\prod';
    }
}
