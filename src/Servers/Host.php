<?php

namespace TruongBo\ProxyRotation\Servers;

final class Host implements HostInterface
{
    public function __construct(
        public readonly string $endpoint,
        public readonly string $method = 'GET',
        public readonly int    $retry_fail_to_next = 1,
        public readonly int    $time_out = 10,
        public readonly bool   $check_exception = true,
        public readonly array  $status_code_to_next = [
            249, 429, 503,
        ],
        public readonly array  $options = [],
    )
    {
    }

    public function __toString(): string
    {
        return $this->endpoint;
    }
}
