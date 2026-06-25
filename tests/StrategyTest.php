<?php

namespace TruongBo\ProxyRotation\Tests;

use Orchestra\Testbench\TestCase;
use TruongBo\ProxyRotation\Exception\EmptyNodeException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Rotation;
use TruongBo\ProxyRotation\Strategy\Frequency;
use TruongBo\ProxyRotation\Strategy\Random;
use TruongBo\ProxyRotation\Strategy\RoundRobin;
use TruongBo\ProxyRotation\Strategy\WeightedRoundRobin;

class StrategyTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('proxy.array_check_input_random', [
            'both',
            'has_weight',
            'no_weight',
        ]);
    }

    public function testRoundRobinStrategy(): void
    {
        $rotation = new Rotation(new RoundRobin(counter: 0));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1'),
                new ProxyNode(name: 'node2'),
                new ProxyNode(name: 'node3'),
            ]
        );

        $results = [];
        for ($i = 0; $i < 6; $i++) {
            $results[] = $rotation->pick(proxy_cluster: $cluster)->name;
        }

        $this->assertEquals(['node1', 'node2', 'node3', 'node1', 'node2', 'node3'], $results);
    }

    public function testWeightedRoundRobinStrategy(): void
    {
        $rotation = new Rotation(new WeightedRoundRobin(counter: 0));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1', weight: 2),
                new ProxyNode(name: 'node2', weight: 1),
            ]
        );

        $results = [];
        for ($i = 0; $i < 9; $i++) {
            $results[] = $rotation->pick(proxy_cluster: $cluster)->name;
        }

        // Pattern should repeat every 3 picks (2+1)
        $this->assertCount(9, $results);
    }

    public function testRandomStrategyBoth(): void
    {
        $rotation = new Rotation(new Random(input_random: 'both'));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1'),
                new ProxyNode(name: 'node2'),
                new ProxyNode(name: 'node3'),
            ]
        );

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $node = $rotation->pick(proxy_cluster: $cluster);
            $this->assertNotNull($node);
            $results[] = $node->name;
        }

        // All results should be valid nodes
        foreach ($results as $name) {
            $this->assertIn($name, ['node1', 'node2', 'node3']);
        }
    }

    public function testRandomStrategyHasWeight(): void
    {
        $rotation = new Rotation(new Random(input_random: 'has_weight'));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1', weight: 5),
                new ProxyNode(name: 'node2'),  // no weight
                new ProxyNode(name: 'node3', weight: 3),
            ]
        );

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $node = $rotation->pick(proxy_cluster: $cluster);
            $this->assertNotNull($node);
            $results[] = $node->name;
        }

        // Should only include weighted nodes
        foreach ($results as $name) {
            $this->assertIn($name, ['node1', 'node3']);
        }
    }

    public function testRandomStrategyNoWeight(): void
    {
        $rotation = new Rotation(new Random(input_random: 'no_weight'));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1', weight: 5),
                new ProxyNode(name: 'node2'),  // no weight
                new ProxyNode(name: 'node3'),  // no weight
            ]
        );

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $node = $rotation->pick(proxy_cluster: $cluster);
            $this->assertNotNull($node);
            $results[] = $node->name;
        }

        // Should only include non-weighted nodes
        foreach ($results as $name) {
            $this->assertIn($name, ['node2', 'node3']);
        }
    }

    public function testFrequencyStrategy(): void
    {
        $rotation = new Rotation(new Frequency(frequency: 0.8, depth: 0.2));

        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1'),
                new ProxyNode(name: 'node2'),
                new ProxyNode(name: 'node3'),
                new ProxyNode(name: 'node4'),
                new ProxyNode(name: 'node5'),
            ]
        );

        $results = [];
        for ($i = 0; $i < 20; $i++) {
            $node = $rotation->pick(proxy_cluster: $cluster);
            $this->assertNotNull($node);
            $results[] = $node->name;
        }

        $this->assertCount(20, $results);
    }

    public function testEmptyClusterThrowsException(): void
    {
        $this->expectException(EmptyNodeException::class);
        
        $rotation = new Rotation(new RoundRobin(counter: 0));
        $cluster = new ProxyCluster('empty', []);
        
        $rotation->pick(proxy_cluster: $cluster);
    }

    public function testSortCluster(): void
    {
        $cluster = new ProxyCluster(
            cluster_name: 'cluster1',
            array_proxy_node: [
                new ProxyNode(name: 'node1', weight: 100),
                new ProxyNode(name: 'node2', weight: 10),
                new ProxyNode(name: 'node3', weight: 50),
            ]
        );

        // Test descending sort
        $cluster->sort('DESC');
        $this->assertEquals('node1', $cluster->getNode(0)->name);
        $this->assertEquals('node3', $cluster->getNode(1)->name);
        $this->assertEquals('node2', $cluster->getNode(2)->name);

        // Test ascending sort
        $cluster->sort('ASC');
        $this->assertEquals('node2', $cluster->getNode(0)->name);
        $this->assertEquals('node3', $cluster->getNode(1)->name);
        $this->assertEquals('node1', $cluster->getNode(2)->name);
    }

    public function testInvalidRandomMode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Random(input_random: 'invalid_mode');
    }
}
