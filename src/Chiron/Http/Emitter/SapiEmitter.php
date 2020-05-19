<?php

declare(strict_types=1);

namespace Chiron\Http\Emitter;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

//https://github.com/cakephp/cakephp/blob/master/src/Http/ResponseEmitter.php

// TODO : Interface à virer elle ne sert pas à grand choses !!!!!
// TODO : ajouter une méthode public ->withoutBody(bool) ou 'shouldOutputBody(bool)' pour gérer le cas de la request méthode === GET, et pour ne pas passer ce booléen lors de la méthode emit, mais bien avant !!!!
// TODO : externaliser la méthode pour définri la tailler du buffer, elle pourra être appeller dans un bootloader pour modifier cette valeur.
final class SapiEmitter implements EmitterInterface
{
    /** @var array list of http code who MUST not have a body */
    private const NO_BODY_RESPONSE_CODES = [204, 205, 304];

    /** @var int default buffer size (8Mb) */
    private const DEFAULT_BUFFER_SIZE = 8 * 1024 * 1024;

    /**
     * Construct the Emitter, and define the chunk size used to emit the body.
     *
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = self::DEFAULT_BUFFER_SIZE)
    {
        if ($bufferSize <= 0) {
            throw new InvalidArgumentException('Buffer size must be greater than zero');
        }

        $this->bufferSize = $bufferSize;
    }

    /**
     * Emit the http response to the client.
     *
     * @param ResponseInterface $response
     */
    // TODO : lever une exception si les headers sont déjà envoyés !!!!
    public function emit(ResponseInterface $response, bool $withoutBody = false): bool
    {
        $withoutBody = $withoutBody || $this->isResponseEmpty($response);

        // TODO : lever une exception si les headers sont déjà envoyés !!!!
        // headers have already been sent by the developer ?
        if (headers_sent() === false) {
            $this->emitHeaders($response);
            // It is important to mention that this method should be called after the headers are sent, in order to prevent PHP from changing the status code of the emitted response.
            $this->emitStatusLine($response);
        }

        if (! $withoutBody) {
            $this->emitBody($response);
        }

        return true;
    }

    /**
     * Send HTTP Headers.
     *
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        foreach ($response->getHeaders() as $name => $values) {
            $first = stripos($name, 'Set-Cookie') === 0 ? false : true;
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                header($header, $first, $statusCode);
                $first = false;
            }
        }
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * It is important to mention that this method should be called after
     * `emitHeaders()` in order to prevent PHP from changing the status code of
     * the emitted response.
     */
    private function emitStatusLine(ResponseInterface $response): void
    {
        $statusLine = sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($statusLine, true, $response->getStatusCode());
    }

    /**
     * Emit the message body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response to emit
     */
    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (! $body->eof()) {
            echo $body->read($this->bufferSize);
        }
    }

    /**
     * Asserts response body data is empty or http status code doesn't require a body.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isResponseEmpty(ResponseInterface $response): bool
    {
        if (in_array($response->getStatusCode(), self::NO_BODY_RESPONSE_CODES, true)) {
            return true;
        }

        $body = $response->getBody();
        $seekable = $body->isSeekable();
        if ($seekable) {
            $body->rewind();
        }

        return $seekable ? $body->read(1) === '' : $body->eof();
    }
}
