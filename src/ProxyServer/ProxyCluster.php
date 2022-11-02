<?php

namespace TruongBo\ProxyRotation\ProxyServer;

use TruongBo\ProxyRotation\Exception\InvalidTypeSortException;
use TruongBo\ProxyRotation\ProxyServer\Traits\WeightCluster;
use Illuminate\Support\Collection;

final class ProxyCluster implements ProxyClusterInterface
{
    use WeightCluster;

    /**
     * @var Collection $proxy_node_collection
     * @var Collection $proxy_node_has_weight
     * @var Collection $proxy_node_no_weight
     * */
    private Collection $proxy_node_collection;
    private Collection $proxy_node_has_weight;
    private Collection $proxy_node_no_weight;

    /**
     * Function construct class ProxyCluster
     *
     * @param string $cluster_name (must unique)
     * @param array $array_proxy_node
     */
    public function __construct(
        public readonly string $cluster_name,
        array                  $array_proxy_node
    )
    {
        $this->proxy_node_collection = collect($array_proxy_node);
        $this->proxy_node_has_weight = collect();
        $this->proxy_node_no_weight = collect();

        $this->handleWeight();
    }

    /**
     * Check empty Cluster
     *
     * @return bool
     * */
    public function isEmpty(): bool
    {
        return $this->proxy_node_collection->isEmpty();
    }

    /**
     * Get node by index collection
     *
     * @param int $index
     * @return ProxyNode|null
     */
    public function getNode(int $index): ProxyNode|null
    {
        return $this->proxy_node_collection->get($index);
    }

    /**
     * Get count node in cluster
     *
     * @return int
     * */
    public function count(): int
    {
        return $this->proxy_node_collection->count() ?? 0;
    }

    /**
     * Sort collection proxy node
     *
     * @param string $type
     * @return ProxyCluster
     * @throws InvalidTypeSortException
     */
    public function sort(string $type = "ASC"): self
    {
        if (strtoupper($type) == "ASC") {
            $this->proxy_node_collection = $this->proxy_node_collection->sortBy('weight');
            $this->proxy_node_no_weight = $this->proxy_node_collection->sortBy('weight');
            $this->proxy_node_has_weight = $this->proxy_node_collection->sortBy('weight');
        } elseif (strtoupper($type) == "DESC") {
            $this->proxy_node_collection = $this->proxy_node_collection->sortByDesc('weight');
            $this->proxy_node_no_weight = $this->proxy_node_collection->sortByDesc('weight');
            $this->proxy_node_has_weight = $this->proxy_node_collection->sortByDesc('weight');
        } else {
            throw new InvalidTypeSortException();
        }

        return $this;
    }
}
