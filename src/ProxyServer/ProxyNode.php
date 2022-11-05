<?php

namespace TruongBo\ProxyRotation\ProxyServer;

use TruongBo\ProxyRotation\Exception\MaxUseNodeException;
use TruongBo\ProxyRotation\ProxyServer\Traits\MaxUse;

final class ProxyNode
{
    use MaxUse;

    /**
     * Function construct class ProxyNode
     *
     * @param string $name
     * @param int $weight
     *
     * If you set values for $max_use and $max_wait_use, when this proxy runs pass the allowed number of times.
     * It will temporarily sleep in $max_wait_use and resume normal use
     * @param int|null $max_use
     * @param int|null $max_wait_use
     *
     * In case of Fail and fail 10 times of proxy in a row, within 90 seconds Fail Timeout will not use this proxy anymore.
     * @param int $max_fail
     * @param int $fail_time_out_second
     * @throws MaxUseNodeException
     */
    public function __construct(
        public readonly string $name,
        public readonly int    $weight = 0,
        public readonly ?int   $max_use = null,
        public readonly ?int   $max_wait_use = null,
        public readonly int    $max_fail = 10,
        public readonly int    $fail_time_out_second = 90
    )
    {
        if ($this->max_use < 2 && $this->max_wait_use < 2) {
            throw new MaxUseNodeException();
        }
    }
}
