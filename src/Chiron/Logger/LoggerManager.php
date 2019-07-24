<?php

declare(strict_types=1);

namespace Chiron\Logger;

use Closure;
use Throwable;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Formatter\FormatterInterface;

class LoggerManager
{
    use ParsesLogConfiguration;

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    //protected $app;
    /**
     * The array of resolved channels.
     *
     * @var array
     */
    protected $channels = [];
    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    // TODO : à virer ????
    protected $customCreators = [];


    // TODO : solution temporaire faire mieux que ca en terme de code !!!!
    private $conf = [];

    private $container;

    private $storagePath;





    /**
     * Create a new Log manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */

    public function __construct($container, $conf, string $storagePath)
    {
        //$this->app = $app;
        $this->container = $container;
        $this->conf = $conf;
        $this->storagePath = $storagePath;
    }

    /**
     * Create a new, on-demand aggregate logger instance.
     *
     * @param  array  $channels
     * @param  string|null  $channel
     * @return \Psr\Log\LoggerInterface
     */
    // TODO : à virer
    public function stack(array $channels, ?string $channel = null): LoggerInterface
    {
        return $this->createStackDriver(compact('channels', 'channel'));
    }
    /**
     * Get a log channel instance.
     *
     * @param  string|null  $channel
     * @return \Psr\Log\LoggerInterface
     */
    public function channel(?string $channel = null): LoggerInterface
    {
        return $this->driver($channel);
    }
    /**
     * Get a log driver instance.
     *
     * @param  string|null  $driver
     * @return \Psr\Log\LoggerInterface
     */
    // TODO : à virer, conserver uniquement la méthode "channel()"
    public function driver(?string $driver = null): LoggerInterface
    {
        return $this->get($driver ?? $this->getDefaultDriver());
    }
    /**
     * Attempt to get the log from the local cache.
     *
     * @param  string  $name
     * @return \Psr\Log\LoggerInterface
     */
    protected function get(string $name): LoggerInterface
    {
        try {
            //return $this->channels[$name] ?? $this->channels[$name] = $this->resolve($name);

            if (isset($this->channels[$name])) {
                return $this->channels[$name];
            }

            return $this->channels[$name] = $this->resolve($name);



        } catch (Throwable $e) {
            $logger = $this->createEmergencyLogger();
            $logger->emergency('Unable to create configured logger. Using emergency logger.', ['exception' => $e]);
        }
    }
    /**
     * Create an emergency log handler to avoid white screens of death.
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createEmergencyLogger(): LoggerInterface
    {
        //TODO : virer le storage_path et utiliser : "php://stderr" non ????
        return new Monolog('chiron', $this->prepareHandlers([new StreamHandler(
                $this->storagePath.'/logs/chiron.log', $this->level(['level' => 'debug'])
        )]));
    }
    /**
     * Resolve the given log instance by name.
     *
     * @param  string  $name
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve(string $name): LoggerInterface
    {
        $config = $this->configurationFor($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Log [{$name}] is not defined.");
        }
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }
        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
    }
    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($config);
    }
    /**
     * Create a custom log driver instance.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createCustomDriver(array $config): LoggerInterface
    {
        $factory = is_callable($via = $config['via']) ? $via : $this->container->build($via);
        return $factory($config);
    }
    /**
     * Create an aggregate log driver instance.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createStackDriver(array $config): LoggerInterface
    {
        /*
        $handlers = collect($config['channels'])->flatMap(function ($channel) {
            return $this->channel($channel)->getHandlers();
        })->all();*/

        $handlers = [];
        foreach ($config['channels'] as $channel) {

            //$handlers[] = $this->channel($channel)->getHandlers();
            $handlers = array_merge($handlers, $this->channel($channel)->getHandlers());
        }

        if ($config['ignore_exceptions'] ?? false) {
            $handlers = [new WhatFailureGroupHandler($handlers)];
        }

        return new Monolog($this->parseChannel($config), $handlers);
    }
    /**
     * Create an instance of the single file log driver.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSingleDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(
                new StreamHandler(
                    $config['path'], $this->level($config),
                    $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
                ), $config
            ),
        ]);
    }
    /**
     * Create an instance of the daily file log driver.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createDailyDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new RotatingFileHandler(
                $config['path'], $config['days'] ?? 7, $this->level($config),
                $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
            ), $config),
        ]);
    }
    /**
     * Create an instance of the Slack log driver.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSlackDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SlackWebhookHandler(
                $config['url'],
                $config['channel'] ?? null,
                $config['username'] ?? 'Chiron Log',
                $config['attachment'] ?? true,
                $config['emoji'] ?? ':boom:',
                $config['short'] ?? false,
                $config['context'] ?? true,
                $this->level($config),
                $config['bubble'] ?? true,
                $config['exclude_fields'] ?? []
            ), $config),
        ]);
    }
    /**
     * Create an instance of the syslog log driver.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSyslogDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SyslogHandler(
                'chiron',
                //Str::snake($this->conf['config']['app.name'], '-'),
                $config['facility'] ?? LOG_USER, $this->level($config)
            ), $config),
        ]);
    }
    /**
     * Create an instance of the "error log" log driver.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createErrorlogDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new ErrorLogHandler(
                $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $this->level($config)
            )),
        ]);
    }
    /**
     * Create an instance of any handler available in Monolog.
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createMonologDriver(array $config): LoggerInterface
    {
        if (! is_a($config['handler'], HandlerInterface::class, true)) {
            throw new InvalidArgumentException(
                $config['handler'].' must be an instance of '.HandlerInterface::class
            );
        }
        $with = array_merge(
            ['level' => $this->level($config)],
            $config['handler_with'] ?? []
        );

        return new Monolog($this->parseChannel($config), [$this->prepareHandler(
            $this->container->build($config['handler'], $with), $config
        )]);
    }
    /**
     * Prepare the handlers for usage by Monolog.
     *
     * @param  array  $handlers
     * @return array
     */
    protected function prepareHandlers(array $handlers): array
    {
        foreach ($handlers as $key => $handler) {
            $handlers[$key] = $this->prepareHandler($handler);
        }
        return $handlers;
    }
    /**
     * Prepare the handler for usage by Monolog.
     *
     * @param  \Monolog\Handler\HandlerInterface  $handler
     * @param  array  $config
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function prepareHandler(HandlerInterface $handler, array $config = []): HandlerInterface
    {
        if (! isset($config['formatter'])) {
            $handler->setFormatter($this->formatter());
        } elseif ($config['formatter'] !== 'default') {
            $handler->setFormatter($this->container->build($config['formatter'], $config['formatter_with'] ?? []));
        }
        return $handler;
    }
    /**
     * Get a Monolog formatter instance.
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function formatter(): FormatterInterface
    {
        $formatter = new LineFormatter(null, null, true, true);
        $formatter->includeStacktraces();

        return $formatter;
    }
    /**
     * Get the log connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function configurationFor(string $name): array
    {
        return $this->conf['channels'][$name];
    }
    /**
     * Get the default log driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->conf['default'];
    }
    /**
     * Set the default log driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        $this->conf['default'] = $name;
    }
    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    // TODO : méthode à virer ????
    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);
        return $this;
    }

    /**
     * Get fallback log channel name.
     *
     * @return string
     */
    protected function getFallbackChannelName(): string
    {
        //return $this->app->bound('env') ? $this->app->environment() : 'production';
        // TODO : améliorer le code !!!!!!
        return 'chironTEMP';
    }
}
