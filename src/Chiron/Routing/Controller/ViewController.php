<?php

declare(strict_types=1);

namespace Chiron\Routing\Controller;

use Chiron\Kernel;
use Chiron\Routing\Route;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Views\TemplateRendererInterface;

class ViewController
{
    /**
     * The view factory implementation.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $renderer;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\View\Factory  $view
     * @return void
     */
    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
    /**
     * Invoke the controller method.
     *
     * @param  array  $args
     * @return \Illuminate\Contracts\View\View
     */
    //public function __invoke(...$args)
    public function __invoke(string $view, array $params)
    {
        //[$view, $params] = array_slice($args, -2);

        return $this->renderer->render($view, $params);
    }
}
