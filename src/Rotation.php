<?php

namespace TruongBo\ProxyRotation;

use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Strategy\StrategyInterface;

final class Rotation implements RotationInterface
{
    /**
     * Function construct class Rotation
     *
     * @param StrategyInterface $strategy
     */
    public function __construct(
        private readonly Strategy\StrategyInterface $strategy
    )
    {
    }

    /**
     * Pick a node proxy by strategy
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @param callable|null $condition_switch
     * @return ProxyNode|null
     */
    public function pick(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): ProxyNode|null
    {
        return $this->strategy->getNode(proxy_cluster: $proxy_cluster, condition_switch: $condition_switch);
    }
}
