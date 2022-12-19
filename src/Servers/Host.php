<?php

namespace TruongBo\ProxyRotation\Servers;

use TruongBo\ProxyRotation\Exception\HostException;

final class Host implements HostInterface
{
    /**
     * @param string $endpoint
     * @param string $method
     * @param int $retry_fail_to_next
     * @param int $time_out
     * @param bool $check_exception
     * @param array $status_code_to_next
     * @param array $options
     * @throws HostException
     */
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
        $this->validateConstructor();
    }

    /**
     * @throws HostException
     */
    public function validateConstructor()
    {
        if($this->retry_fail_to_next < 1){
            throw new HostException("\$retry_fail_to_next must be greater than 1");
        }

        if($this->time_out < 1){
            throw new HostException("\$retry_fail_to_next must be greater than 1");
        }
    }

    public function __toString(): string
    {
        return $this->endpoint;
    }
}
