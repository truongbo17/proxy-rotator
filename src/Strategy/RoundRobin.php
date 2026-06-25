<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

class RoundRobin implements StrategyInterface
{
    private const MAX_RETRIES = 100;

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
     * @param ProxyClusterInterface $proxy_cluster
     * @param callable|null $condition_switch
     * @return ProxyNode|null
     * @throws EmptyNodeException
     */
    public function getNode(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): null|ProxyNode
    {
        if ($proxy_cluster->isEmpty()) {
            throw new EmptyNodeException();
        }

        return $this->getNodeWithRetry($proxy_cluster);
    }

    /**
     * Attempt to get a non-throttled node with retry logic
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    private function getNodeWithRetry(ProxyClusterInterface $proxy_cluster): ProxyNode
    {
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $index = $this->counter++ % $proxy_cluster->count();
            $proxy_node = $proxy_cluster->getNode(index: $index);
            
            if ($proxy_node && !($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class))) {
                return $proxy_node;
            }
        }

        throw new EmptyNodeException('All proxies are throttled after ' . self::MAX_RETRIES . ' attempts');
    }
}
