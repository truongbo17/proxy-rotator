<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

class Random implements StrategyInterface
{
    /**
     * Construct function Class RoundRobin
     *
     * @param string $input_random
     */
    public function __construct(
        private string $input_random = "both"
    )
    {
        if (!in_array($this->input_random, config('proxy.array_check_input_random'))) $this->input_random = "both";
    }

    /**
     * Get node by strategy Random (consists of has weight and no weight)
     *
     * @param ProxyCluster $proxy_cluster
     * @param callable|null $condition_switch
     * @return ?ProxyNode
     * @throws EmptyNodeException
     */
    public function getNode(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): ?ProxyNode
    {
        if ($proxy_cluster->isEmpty()) {
            throw new EmptyNodeException();
        }

        return match ($this->input_random) {
            "both" => $this->bothRandom(proxy_cluster: $proxy_cluster),
            "has_weight" => $this->hasWeightRandom(proxy_cluster: $proxy_cluster),
            "no_weight" => $this->noWeightRandom(proxy_cluster: $proxy_cluster),
        };
    }

    /**
     * Get node by strategy Random (both)
     *
     * @param ProxyCluster $proxy_cluster
     * @return ?ProxyNode
     */
    public function bothRandom(ProxyCluster $proxy_cluster): ?ProxyNode
    {
        re_get_node:
        $index = mt_rand(0, $proxy_cluster->count() - 1);
        $proxy_node = $proxy_cluster->getNode(index: $index);
        if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
            goto re_get_node;
        }

        return $proxy_node;
    }

    /**
     * Get node by strategy Random (has weight)
     *
     * @param ProxyCluster $proxy_cluster
     * @return ?ProxyNode
     * @throws EmptyNodeException
     */
    public function hasWeightRandom(ProxyCluster $proxy_cluster): ?ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeHasWeight()) {
            throw new EmptyNodeException(message: "No node has weight . Please increase weight for node");
        }

        re_get_node:
        $index = mt_rand(0, $proxy_cluster->countNodeHasWeight() - 1);
        $proxy_node = $proxy_cluster->getNodeHasWeight(index: $index);
        if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
            goto re_get_node;
        }

        return $proxy_node;
    }

    /**
     * Get node by strategy Random (no weight)
     *
     * @param ProxyCluster $proxy_cluster
     * @return ?ProxyNode
     * @throws EmptyNodeException
     */
    public function noWeightRandom(ProxyCluster $proxy_cluster): ?ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeHasWeight()) {
            throw new EmptyNodeException(message: "No node no weight . Please set the weight of proxy node to 0");
        }

        re_get_node:
        $index = mt_rand(0, $proxy_cluster->countNodeNoWeight() - 1);
        $proxy_node = $proxy_cluster->getNodeNoWeight(index: $index);
        if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
            goto re_get_node;
        }

        return $proxy_node;
    }
}
