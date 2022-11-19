<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class EmptyHostException extends Exception
{
    public function __construct(string $message = 'Hosts cannot be left blank,please add a host.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
