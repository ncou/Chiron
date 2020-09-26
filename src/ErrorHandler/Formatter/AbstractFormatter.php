<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler\Formatter;

use Chiron\Http\Exception\HttpException;
use Throwable;

abstract class AbstractFormatter implements FormatterInterface
{
    /** @var int */
    protected $defaultErrorStatusCode = 500;

    /** @var string */
    protected $defaultErrorTitle = 'Chiron Application Error';

    /** @var string */
    //protected $defaultErrorDetail = 'A website error has occurred. Sorry for the temporary inconvenience.';
    protected $defaultErrorDetail = 'Whoops, looks like something went wrong.'; //'Hm... Unfortunately, the server crashed. Apologies.'

    protected function getErrorTitle(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getTitle();
        }

        return $this->defaultErrorTitle;
    }

    protected function getErrorDetail(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getDetail();
        }

        return $this->defaultErrorDetail;
    }

    protected function getErrorStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return $this->defaultErrorStatusCode;
    }
}
