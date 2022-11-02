<?php

namespace TruongBo\ProxyRotation\Exception;

use Exception;

final class EmptyNodeException extends Exception
{
    public function __construct(string $message = 'Cluster must have at least one node.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
