<?php
/**
 * @see       https://github.com/zendframework/zend-stratigility for the canonical source repository
 *
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-stratigility/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Chiron\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FixedResponseHandler implements RequestHandlerInterface
{
    /**
     * fixed response to return.
     *
     * @var ResponseInterface
     */
    private $fixedResponse;

    /**
     * @param ResponseInterface $response always return this response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->fixedResponse = $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\MissingResponseException if the decorated middleware
     *                                            fails to produce a response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->fixedResponse;
    }
}
