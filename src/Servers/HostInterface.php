<?php

namespace TruongBo\ProxyRotation\Servers;

use TruongBo\ProxyRotation\Exception\HostException;

interface HostInterface
{
    /**
     * @throws HostException
     */
    public function validateConstructor();

    /**
     * Add retry logic
     * @param callable $retry_logic
     */
    public function addRetryLogic(callable $retry_logic): void;

    /**
     * Get retry logic
     *
     * @return callable|null
     * */
    public function getRetryLogic(): callable|null;
}
