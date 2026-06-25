<?php

/**
 * Author truongbo & denisyukphp (only this class Frequency Strategy)
 * */

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

final class Frequency implements StrategyInterface
{
    private const MAX_RETRIES = 100;

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
            $total = $proxy_cluster->count();
            $low = (int)ceil($this->depth * $total);
            $high = $low + ((1 < $total) ? 1 : 0);

            $index = $this->isChance($this->frequency) ? mt_rand(1, $low) : mt_rand($high, $total);
            $proxy_node = $proxy_cluster->getNode(index: $index - 1);
            
            if ($proxy_node && !($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class))) {
                return $proxy_node;
            }
        }

        throw new EmptyNodeException('All proxies are throttled after ' . self::MAX_RETRIES . ' attempts');
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
