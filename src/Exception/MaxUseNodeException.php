<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class MaxUseNodeException extends Exception
{
    public function __construct(string $message = '$max_use & $max_wait_use of ProxyNode must be greater than 1.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
