<?php

/**
 * Author truongbo & denisyukphp
 * */

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

final class Frequency implements StrategyInterface
{
    /**
     * Function construct class Frequency
     *
     * @param float $frequency
     * @param float $depth
     */
    public function __construct(
        private readonly float $frequency = 0.8,
        private readonly float $depth = 0.2,
    )
    {
    }

    /**
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
        $total = $proxy_cluster->count();
        $low = (int)ceil($this->depth * $total);
        $high = $low + ((1 < $total) ? 1 : 0);

        $index = $this->isChance($this->frequency) ? mt_rand(1, $low) : mt_rand($high, $total);
        $proxy_node = $proxy_cluster->getNode(index: $index - 1);
        if ($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class)) {
            goto re_get_node;
        }

        return $proxy_node;
    }

    /**
     * Random Frequency
     *
     * @param float $frequency
     * @return bool
     */
    private function isChance(float $frequency): bool
    {
        return $frequency * 100 >= mt_rand(1, 100);
    }
}
