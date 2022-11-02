<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

final class WeightedRoundRobin implements StrategyInterface
{
    /**
     * @var int $counter_node_weight
     * */
    private int $counter_node_weight = 0;
    private ?int $index_node_weight = null;

    /**
     * Construct function Class RoundRobin
     *
     * @param int $counter
     */
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
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    public function getNode(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeHasWeight()) {
            throw new EmptyNodeException(message: "No node has weight . Please increase weight for node");
        }

        if ($this->counter_node_weight < 1) {
            re_get_node:
            $index = $this->counter++ % $proxy_cluster->countNodeHasWeight();
            $proxy_node = $proxy_cluster->getNodeHasWeight(index: $index);

            if ($proxy_node->weight > 1) {
                $this->counter_node_weight = $proxy_node->weight - 1;
                $this->index_node_weight = $index;
            } else {
                return $proxy_node;
            }
        } else {
            $this->counter_node_weight--;
        }
        return $proxy_cluster->getNodeHasWeight(index: $this->index_node_weight);
    }
}
