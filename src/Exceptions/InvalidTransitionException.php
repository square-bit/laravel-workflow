<?php

namespace Squarebit\Workflows\Exceptions;

use Exception;

class InvalidTransitionException extends Exception
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? __('Invalid transition'));
    }
}
