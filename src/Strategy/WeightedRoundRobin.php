<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

final class WeightedRoundRobin implements StrategyInterface
{
    private const MAX_RETRIES = 100;
    /**
     * @var array<int, int> Track weight consumption per index to avoid state issues
     */
    private array $weightState = [];

    /**
     * Construct function Class WeightedRoundRobin
     *
     * @param int $counter
     */
    public function __construct(
        private int $counter = 0
    )
    {
    }

    /**
     * Get node by strategy Weighted Round Robin
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @param callable|null $condition_switch
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    public function getNode(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeHasWeight()) {
            throw new EmptyNodeException('No node has weight. Please increase weight for node');
        }

        return $this->getNodeWithRetry($proxy_cluster);
    }

    /**
     * Attempt to get a non-throttled weighted node with retry logic
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    private function getNodeWithRetry(ProxyClusterInterface $proxy_cluster): ProxyNode
    {
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $index = $this->counter++ % $proxy_cluster->countNodeHasWeight();
            $proxy_node = $proxy_cluster->getNodeHasWeight(index: $index);

            if (!$proxy_node) {
                continue;
            }

            // Check if this node is throttled
            if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
                continue;
            }

            // Return the node, respecting its weight
            return $proxy_node;
        }

        throw new EmptyNodeException('All proxies are throttled after ' . self::MAX_RETRIES . ' attempts');
    }
}
