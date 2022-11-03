<?php

namespace TruongBo\ProxyRotation\ProxyServer\Traits;

use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

trait WeightCluster
{
    /**
     * Handle proxy node , weighted and unweighted proxies
     *
     * @return void
     */
    public function handleWeight(): void
    {
        $this->proxy_node_collection->each(function ($value) {
            if ((int)$value->weight > 0) {
                $this->proxy_node_has_weight->push($value);
            } else {
                $this->proxy_node_no_weight->push($value);
            }
        });
    }

    /**
     * Get node has weight by index collection
     *
     * @param int $index
     * @return ProxyNode|null
     */
    public function getNodeHasWeight(int $index): ProxyNode|null
    {
        return $this->proxy_node_has_weight->get($index);
    }

    /**
     * Get node no weight by index collection
     *
     * @param int $index
     * @return ProxyNode|null
     */
    public function getNodeNoWeight(int $index): ProxyNode|null
    {
        return $this->proxy_node_no_weight->get($index);
    }

    /**
     * Check empty Node Has Weight
     *
     * @return bool
     * */
    public function isEmptyNodeHasWeight(): bool
    {
        return $this->proxy_node_has_weight->isEmpty();
    }

    /** Check empty Node No Weight
     *
     * @return bool
     * */
    public function isEmptyNodeNoWeight(): bool
    {
        return $this->proxy_node_no_weight->isEmpty();
    }

    /**
     * Return count node has weight by index collection
     *
     * @return int
     */
    public function countNodeHasWeight(): int
    {
        return $this->proxy_node_has_weight->count();
    }

    /**
     * Return count node no weight by index collection
     *
     * @return int
     */
    public function countNodeNoWeight(): int
    {
        return $this->proxy_node_no_weight->count();
    }
}
