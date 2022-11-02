<?php

namespace TruongBo\ProxyRotation;

use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

interface RotationInterface
{
    public function pick(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): ProxyNode|null;
}
