<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

interface StrategyInterface
{
    public function getNode(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): null|ProxyNode;
}
