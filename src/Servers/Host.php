<?php

namespace TruongBo\ProxyRotation\Servers;

use TruongBo\ProxyRotation\Exception\HostException;
use TruongBo\ProxyRotation\Exception\InvalidCallableException;

final class Host implements HostInterface
{
    /**
     * @var callable $retry_logic
     * */
    private $retry_logic = null;

    /**
     * Function constructor
     *
     * @param string $endpoint
     * @param string $method
     * @param int $retry_fail_to_next
     * @param int $time_out
     * @param int $sleep_to_next_request
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
        public readonly int    $sleep_to_next_request = 0,
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
     * Validate constructor class
     * @throws HostException
     */
    public function validateConstructor()
    {
        if ($this->retry_fail_to_next < 1) {
            throw new HostException("\$retry_fail_to_next must be greater than 1");
        }

        if ($this->time_out < 1) {
            throw new HostException("\$time_out must be greater than 1");
        }

        if ($this->sleep_to_next_request < 0) {
            throw new HostException("\$sleep_to_next_request must be greater than 0");
        }
    }

    /**
     * Add retry logic
     * @param callable $retry_logic
     * @throws InvalidCallableException
     */
    public function addRetryLogic(callable $retry_logic): void
    {
        if (!is_callable($retry_logic)) {
            throw new InvalidCallableException();
        }

        $this->retry_logic = $retry_logic;
    }

    /**
     * Get retry logic
     *
     * @return callable|null
     * */
    public function getRetryLogic(): callable|null
    {
        return $this->retry_logic;
    }

    /**
     * Sleep to next request
     *
     * @return void
     * */
    public function sleep(): void
    {
        sleep($this->sleep_to_next_request);
    }

    public function __toString(): string
    {
        return $this->endpoint;
    }
}
