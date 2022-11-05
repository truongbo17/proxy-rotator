<?php

namespace TruongBo\ProxyRotation\Strategy;

use TruongBo\ProxyRotation\Exception\EmptyStrategyException;
use TruongBo\ProxyRotation\Exception\InvalidCallableException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use Illuminate\Support\Collection;

final class MultipleDynamic implements StrategyInterface
{
    /**
     * @var array $strategies
     * */
    private array $strategies;

    private int $array_key_strategy = 0;

    /**
     * @throws EmptyStrategyException
     */
    public function __construct(StrategyInterface ...$strategies)
    {
        if (count($strategies) < 1) {
            throw new EmptyStrategyException();
        }

        $this->strategies = $strategies;
    }

    /**
     * Get node from strategy
     *
     * @param ProxyCluster $proxy_cluster
     * @param callable|null $condition_switch
     * @return ProxyNode|null
     * @throws InvalidCallableException
     */
    public function getNode(ProxyCluster $proxy_cluster, ?callable $condition_switch = null): null|ProxyNode
    {
        $current_proxy_node = $this->strategies[$this->array_key_strategy];
        $proxy_node = $current_proxy_node->getNode($proxy_cluster);
        $check = call_user_func($condition_switch, $proxy_node);

        if (gettype($check) != "boolean") {
            throw new InvalidCallableException();
        }

        if ($check) {
            if($this->array_key_strategy >= count($this->strategies) - 1){
                $this->array_key_strategy = 0;
            }else{
                $this->array_key_strategy++;
            }

            $current_proxy_node = $this->strategies[$this->array_key_strategy];
            return $current_proxy_node->getNode($proxy_cluster);
        } else {
            return $proxy_node;
        }
    }
}
