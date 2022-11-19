<?php

namespace TruongBo\ProxyRotation;

use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

interface RotationInterface
{
    public function pick(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): ProxyNode|null;
}
