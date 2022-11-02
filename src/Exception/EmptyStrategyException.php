<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class EmptyStrategyException extends Exception
{
    public function __construct(string $message = 'Invalid Strategy.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
