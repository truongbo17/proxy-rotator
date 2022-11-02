<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class InvalidCallableException extends Exception
{
    public function __construct(string $message = 'Callable must return a boolean value', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
