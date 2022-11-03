<?php

namespace TruongBo\ProxyRotation\Tests;

use Orchestra\Testbench\TestCase;
use TruongBo\ProxyRotation\Exception\MaxUseNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Rotation;
use TruongBo\ProxyRotation\Strategy\Random;

class RotationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('proxy.array_check_input_random', [
            "both",
            'has_weight',
            'no_weight',
        ]);
    }

    /**
     * @throws MaxUseNodeException
     */
    public function testPick(): void
    {
        $rotation = new Rotation(
            new Random(input_random: "no_weight")
        );

        $proxy_node = new ProxyNode(name: 'proxy-node1');

        $cluster_proxy = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                $proxy_node
            ]
        );

        $this->assertSame($proxy_node, $rotation->pick(proxy_cluster: $cluster_proxy));
    }
}
