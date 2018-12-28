<?php

declare(strict_types=1);

namespace Chiron\Handler\Reporter;

use Raven_Client;
use Throwable;

//https://github.com/thephpleague/booboo/blob/master/src/Handler/SentryHandler.php

class SentryReporter implements ReporterInterface
{
    private $config;

    public function __construct(array $config)
    {
        $config = $this->extendConfig($config);

        $this->config = $config;
    }

    public function report(Throwable $e): void
    {
        $options = $this->config['sentry_options'];
        $raven = new Raven_Client(
            $this->config['dsn'],
            $options
        );
        $data = null;
        if (isset($options['add_context']) && is_callable($options['add_context'])) {
            $data = $options['add_context']($e);
        }

        $raven->captureException($e, $data);
    }

    private function extendConfig(array $config)
    {
        if (! isset($config['sentry_options'])) {
            $config['sentry_options'] = [];
        }
        if (! isset($config['sentry_options']['tags'])) {
            $config['sentry_options']['tags'] = [];
        }
        if (! isset($config['sentry_options']['tags']['php_version'])) {
            $config['sentry_options']['tags']['php_version'] = phpversion();
        }
        if (! isset($config['sentry_options']['tags']['environment'])) {
            $config['sentry_options']['tags']['environment'] = app()->environment();
        }

        return $config;
    }

    /**
     * Can we report the exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public function canReport(Throwable $e): bool
    {
        // check if Sentry client is installed.
        return class_exists(Raven_Client::class);
    }
}
