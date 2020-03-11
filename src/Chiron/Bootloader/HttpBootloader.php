<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Http\Response\HtmlResponse;
use Chiron\Http\Http;
use Psr\Container\ContainerInterface;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Bootload\BootloaderInterface;
use LogicException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Chiron\Config\Config;
use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Container\BindingInterface;

use Chiron\Http\Config\HttpConfig;


class HttpBootloader implements BootloaderInterface
{
    public function boot(Http $http, HttpConfig $httpConfig)
    {
        // add the middlewares.
        foreach ($httpConfig->getMiddlewares() as $middleware) {
            $http->middleware($middleware);
        }
    }
}

