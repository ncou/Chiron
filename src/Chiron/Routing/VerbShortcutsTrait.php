<?php

namespace Chiron\Routing;

trait VerbShortcutsTrait
{
    public function get(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('GET');
    }

    public function head(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('HEAD');
    }

    public function post(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('POST');
    }

    public function patch(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('PATCH');
    }

    public function put(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('PUT');
    }

    public function delete(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('DELETE');
    }

    public function options(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->method('OPTIONS');
    }

    public function any(string $pattern, $handler) : Route
    {
        return $this->map($pattern, $handler)->setAllowedMethods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
    }
}
