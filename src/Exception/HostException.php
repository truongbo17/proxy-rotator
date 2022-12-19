<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class HostException extends Exception
{
    public function __construct(string $message = 'Constructor Host Fail', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
