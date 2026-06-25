<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

class Random implements StrategyInterface
{
    private const MAX_RETRIES = 100;
    private const VALID_MODES = ['both', 'has_weight', 'no_weight'];

    /**
     * Construct function Class Random
     *
     * @param string $input_random
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private string $input_random = 'both'
    )
    {
        if (!in_array($this->input_random, self::VALID_MODES, true)) {
            throw new \InvalidArgumentException(
                'Invalid input_random mode: ' . $this->input_random . 
                '. Must be one of: ' . implode(', ', self::VALID_MODES)
            );
        }
    }

    /**
     * Get node by strategy Random (consists of has weight and no weight)
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @param callable|null $condition_switch
     * @return ?ProxyNode
     * @throws EmptyNodeException
     */
    public function getNode(ProxyClusterInterface $proxy_cluster, ?callable $condition_switch = null): ?ProxyNode
    {
        if ($proxy_cluster->isEmpty()) {
            throw new EmptyNodeException();
        }

        return match ($this->input_random) {
            'both' => $this->bothRandom(proxy_cluster: $proxy_cluster),
            'has_weight' => $this->hasWeightRandom(proxy_cluster: $proxy_cluster),
            'no_weight' => $this->noWeightRandom(proxy_cluster: $proxy_cluster),
        };
    }

    /**
     * Get node by strategy Random (both)
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    public function bothRandom(ProxyClusterInterface $proxy_cluster): ProxyNode
    {
        if ($proxy_cluster->isEmpty()) {
            throw new EmptyNodeException('Proxy cluster is empty');
        }

        return $this->getRandomNodeWithRetry(
            $proxy_cluster,
            0,
            $proxy_cluster->count() - 1,
            fn($idx) => $proxy_cluster->getNode(index: $idx)
        );
    }

    /**
     * Get node by strategy Random (has weight)
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    public function hasWeightRandom(ProxyClusterInterface $proxy_cluster): ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeHasWeight()) {
            throw new EmptyNodeException('No node has weight. Please increase weight for node');
        }

        return $this->getRandomNodeWithRetry(
            $proxy_cluster,
            0,
            $proxy_cluster->countNodeHasWeight() - 1,
            fn($idx) => $proxy_cluster->getNodeHasWeight(index: $idx)
        );
    }

    /**
     * Get node by strategy Random (no weight)
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    public function noWeightRandom(ProxyClusterInterface $proxy_cluster): ProxyNode
    {
        if ($proxy_cluster->isEmptyNodeNoWeight()) {
            throw new EmptyNodeException('No node without weight. Please set the weight of proxy node to 0');
        }

        return $this->getRandomNodeWithRetry(
            $proxy_cluster,
            0,
            $proxy_cluster->countNodeNoWeight() - 1,
            fn($idx) => $proxy_cluster->getNodeNoWeight(index: $idx)
        );
    }

    /**
     * Get a random node with retry logic to avoid throttled proxies
     *
     * @param ProxyClusterInterface $proxy_cluster
     * @param int $min
     * @param int $max
     * @param callable $getNodeFn
     * @return ProxyNode
     * @throws EmptyNodeException
     */
    private function getRandomNodeWithRetry(
        ProxyClusterInterface $proxy_cluster,
        int $min,
        int $max,
        callable $getNodeFn
    ): ProxyNode {
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $index = mt_rand($min, $max);
            $proxy_node = $getNodeFn($index);

            if ($proxy_node && !($proxy_node->hasCheckMaxUse(class_name: self::class) && $proxy_node->checkCounter(class_name: self::class))) {
                return $proxy_node;
            }
        }

        throw new EmptyNodeException('All proxies are throttled after ' . self::MAX_RETRIES . ' attempts');
    }
}
