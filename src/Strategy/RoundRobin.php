<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

class RoundRobin implements StrategyInterface
{
    /**
     * Construct function Class RoundRobin
     *
     * @param int $counter
     * */
    public function __construct(
        private int $counter = 0
    )
    {
    }

    /**
     * Get node by strategy Round Robin
     *
     * @param ProxyCluster $proxy_cluster
     * @param callable|null $condition_switch
     * @return ProxyNode|null
     * @throws EmptyNodeException
     */
    public function getNode(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): null|ProxyNode
    {
        if ($proxy_cluster->isEmpty()) {
            throw new EmptyNodeException();
        }

        re_get_node:
        $index = $this->counter++ % $proxy_cluster->count();
        $proxy_node = $proxy_cluster->getNode(index: $index);
        if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
            goto re_get_node;
        }

        return $proxy_node;
    }
}
