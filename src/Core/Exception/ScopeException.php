<?php

declare(strict_types=1);

namespace Chiron\Core\Exception;

// TODO : créer une CoreException ou CoreRuntimeException qui hérite de RuntimeException, et modifier la classe ScopeException poru qu'elle étende de CoreException.
// TODO : renommer la classe ScopeException en BadScopeException ou InvalidScopeException

class ScopeException extends \LogicException
{
}
