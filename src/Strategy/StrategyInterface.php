<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

interface StrategyInterface
{
    public function getNode(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): null|ProxyNode;
}
