<?php

namespace TruongBo\ProxyRotation\Tests;

use GuzzleHttp\Psr7\Request;
use Orchestra\Testbench\TestCase;
use TruongBo\ProxyRotation\Middleware\ProxyMiddleware;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Rotation;
use TruongBo\ProxyRotation\Strategy\RoundRobin;

class MiddlewareTest extends TestCase
{
    public function testProxyMiddlewareInjectsProxy(): void
    {
        $rotation = new Rotation(new RoundRobin(counter: 0));
        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'http://proxy1.example.com:8080'),
                new ProxyNode(name: 'http://proxy2.example.com:8080'),
            ]
        );

        $middleware = new ProxyMiddleware($rotation, $cluster);
        $handler = function ($request, $options) {
            return $options;
        };

        $callable = $middleware($handler);
        $request = new Request('GET', 'https://example.com');
        $options = ['timeout' => 30];

        $result = $callable($request, $options);

        $this->assertArrayHasKey('proxy', $result);
        $this->assertEquals('http://proxy1.example.com:8080', $result['proxy']);
    }

    public function testProxyMiddlewareThrowsOnEmptyCluster(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Proxy rotation failed/');

        $rotation = new Rotation(new RoundRobin(counter: 0));
        $cluster = new ProxyCluster('empty', []);

        $middleware = new ProxyMiddleware($rotation, $cluster);
        $handler = function ($request, $options) {
            return $options;
        };

        $callable = $middleware($handler);
        $request = new Request('GET', 'https://example.com');
        $options = [];

        $callable($request, $options);
    }
}
