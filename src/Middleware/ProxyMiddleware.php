<?php

namespace TruongBo\ProxyRotation\Middleware;

use Closure;
use Psr\Http\Message\RequestInterface;
use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyClusterInterface;
use TruongBo\ProxyRotation\RotationInterface;

final class ProxyMiddleware
{
    private RotationInterface $rotation;
    private ProxyClusterInterface $proxy_cluster;

    public function __construct(RotationInterface $rotation, ProxyClusterInterface $proxy_cluster)
    {
        $this->rotation = $rotation;
        $this->proxy_cluster = $proxy_cluster;
    }

    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            try {
                $node = $this->rotation->pick(proxy_cluster: $this->proxy_cluster);
                
                if ($node === null) {
                    throw new \RuntimeException('No available proxy node returned from rotation strategy');
                }
                
                $options['proxy'] = $node->name;
            } catch (EmptyNodeException $e) {
                throw new \RuntimeException('Proxy rotation failed: ' . $e->getMessage(), 0, $e);
            }
            
            return $handler($request, $options);
        };
    }
}
