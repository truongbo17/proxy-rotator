<?php

namespace TruongBo\ProxyRotation\Tests;

use Exception;
use Orchestra\Testbench\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use TruongBo\ProxyRotation\Exception\EmptyHostException;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Rotation;
use TruongBo\ProxyRotation\Servers\Client;
use TruongBo\ProxyRotation\Servers\Host;
use TruongBo\ProxyRotation\Strategy\Random;

class Test extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('proxy.array_check_input_random', [
            "both",
            'has_weight',
            'no_weight',
        ]);
    }

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

    /**
     * @throws EmptyHostException
     * @throws Exception
     */
    public function testMultiple(): void
    {
        //Assuming this host is not real
        $first_host = new Host(
            endpoint: "https://httpbin.test/ip",
            method: "GET",
            retry_fail_to_next: 1,
        );

        //Assuming this host is not real
        $second_host = new Host(
            endpoint: "https://httpbinnnnnnnnnnnnnnnnnn.org/ip",
            method: "GET",
            retry_fail_to_next: 3,
        );

        //Assuming this host is usable and returns data
        $third_host = new Host(
            endpoint: "https://httpbin.org/ip",
            method: "GET",
            retry_fail_to_next: 3,
        );

        //Send request
        $client = new Client(
            [
                'stop_when_run_all' => true,
                'debug' => true,
            ],
            $first_host, $second_host, $third_host
        );

        /**
         * @return ResponseInterface;
         * */
        $response = $client->send();

        $status_code_result = $response->getStatusCode();

        $this->assertSame($status_code_result, SymfonyResponse::HTTP_OK);
    }
}
