<?php

namespace TruongBo\ProxyRotation\Middleware;

use Closure;

use Psr\Http\Message\RequestInterface;
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
            $node = $this->rotation->pick($this->proxy_cluster);
            $options['proxy'] = $node->name;
            return $handler($request, $options);
        };
    }
}
