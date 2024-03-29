<?php

namespace Squarebit\Workflows\Exceptions;

use Exception;
use Throwable;

class UnauthorizedTransitionException extends Exception
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? __('Unauthorized transition'), 403, $previous);
    }
}
