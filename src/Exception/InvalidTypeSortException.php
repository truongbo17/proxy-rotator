<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class InvalidTypeSortException extends Exception
{
    public function __construct(string $message = 'Type sort proxy node must ASC or DESC.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
