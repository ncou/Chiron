<?php

declare(strict_types=1);

namespace Chiron\Exception\Reporter;

use Bugsnag\Client;
use Throwable;

class BugsnagReporter implements ReporterInterface
{
    /** @var Client */
    protected $client;

    /**
     * BugsnagReporter constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = app(Client::class);
    }

    /**
     * Report exception.
     *
     * @param Throwable $e
     */
    public function report(Throwable $e): void
    {
        $this->client->notifyException($e);
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
        // check if Bugsnag client is installed.
        return class_exists(Client::class);
    }
}
