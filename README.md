[# Proxy Rotation

<div style="text-align:center;"><img src="./img.png"  alt="Proxy Rotation Logo"/></div>

### Description
* Implement Nginx simple load balancing strategies to build a Package Proxy Rotation to use with Guzzle or anymore...
* Use only effective, consistent and simple strategies to rotate proxies
------
### Optional Features
- [Installation](#installation)
- [Quick Use](#quick-use)
- [Sort Nodes](#sort-nodes)
- [Max Use](#max-use)

### Strategy
- [Random](#random)
- [Frequency](#frequency)
- [Round Robin](#round-robin)
- [Weighted Round Robin](#weighted-round-robin)
- [Multiple Dynamic](#multiple-dynamic)

## Next feature
- Use backup hosts (if the main host is not accessible, Guzzle will automatically connect to the backup hosts to get data...)
- Configure strategies to run separately with hosts, smarter
- Automatically retry connecting hosts when it fails to connect (configure number of retries, response code types)
- Load balancing, apply separate strategies to each Cluster
- Anymore...


-------------------
## Installation
Install the package:

```php
composer require truongbo/proxy-rotation
```
Publish package files (config):

```php
php artisan vendor:publish --provider="TruongBo\ProxyRotation\ProxyRotationServiceProvider"
```

-------------------
## Quick Use

You need to choose a rotation strategy and configure it with Rotation:
```php
use TruongBo\ProxyRotation;
use TruongBo\ProxyRotation\Strategy\RoundRobin;

$rotation = new Rotation(new RoundRobin(counter: 0));
```

Initialize a cluster consisting of proxy nodes:
```php
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1'),
            new ProxyNode(name: 'proxy-node2'),
            new ProxyNode(name: 'proxy-node3'),
            new ProxyNode(name: 'proxy-node4'),
        ]);
```

Then, we should connect ProxyMiddleware to Guzzle for the proxy balancing to work:
```php
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use TruongBo\ProxyRotation\Middleware\ProxyMiddleware;

$stack = HandlerStack::create();
$stack->push(new ProxyMiddleware(rotation: $rotation,proxy_cluster: $proxy_cluster));

$client = new Client([
    'handler' => $stack,
]);
```

Finished, check it out now:
```php
while (true) {
    /** @var ResponseInterface $response */
    $response = $client->get('https://httpbin.org/ip');

    // ...
}
```
Result: (With proxy-node as your proxy address)
```text
+-------------+
| proxy-node1 |
| proxy-node2 |
| proxy-node3 |
| proxy-node4 |
| proxy-node1 |
| proxy-node2 |
| proxy-node3 |
| proxy-node4 |
| etc...      |
+-------------+
```

-------------------
## Sort Nodes
Sort to adjust the order of nodes in ascending or descending order:
```php
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1',weight: 1000),
            new ProxyNode(name: 'proxy-node2',weight: 20),
            new ProxyNode(name: 'proxy-node3',weight: 200),
            new ProxyNode(name: 'proxy-node4',weight: 10),
        ]);
        
$cluster->sort("desc");
```
Top proxy nodes will be used more and vice versa, for example:
```text
+-------------+-------------+
| name        | weight      |
+-------------+-------------+
| proxy-node1 | 1000        |
| proxy-node3 | 200         |
| proxy-node2 | 20          |
| proxy-node4 | 10          |
+-------------+-------------+
```
Sorting nodes can help you use the [Frequency] strategy better.
Use asc for reverse sort.
-------------------

## Max Use

* Store the proxy usage count in the cache and count it. If it is used more than allowed, the proxy will be temporarily idle for the configured time period.
* Use only with Random , Frequency and RoundRobin strategies

Example: 
```php
$cluster = new ProxyCluster('cluster1', [
            new ProxyNode(name: 'node1',weight: 3,max_use: 4,max_use_wait: 10),
            new ProxyNode(name: 'node2',weight: 1,max_use: 2,max_use_wait: 20),
        ]);
```
Everything else runs automatically

------------------
# Random
- $input_random : How are proxies random?
```txt
- both : All proxy nodes are random
- has_weight : Only the weighted proxy node will be random
- no_weight : Only proxy nodes without weights can be random
```
Config:
```php
use TruongBo\ProxyRotation;
use TruongBo\ProxyRotation\Strategy\Random;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$rotation = new Rotation(new Random(input_random: "both"));

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1',weight: 20),
            new ProxyNode(name: 'proxy-node2'),
            new ProxyNode(name: 'proxy-node3'),
            new ProxyNode(name: 'proxy-node4',weight: 100),
        ]);
```
Output(both): 
```text
+-------------+-------------+
| name        | weight      |
+-------------+-------------+
| proxy-node3 | 0           |
| proxy-node1 | 20          |
| proxy-node2 | 0           |
| proxy-node4 | 100         |
+-------------+-------------+
```
----------

## Frequency

More efficient using sort node
```php
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;
use TruongBo\ProxyRotation\Strategy\Frequency;

$rotation = new Rotation(new Frequency(frequency: 0.8, depth: 0.2));

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1',weight: 2048),
            new ProxyNode(name: 'proxy-node2',weight: 1024),
            new ProxyNode(name: 'proxy-node3',weight: 512),
            new ProxyNode(name: 'proxy-node4',weight: 256),
            new ProxyNode(name: 'proxy-node5',weight: 256),
            new ProxyNode(name: 'proxy-node6',weight: 64),
            new ProxyNode(name: 'proxy-node7',weight: 32),
            new ProxyNode(name: 'proxy-node8',weight: 16),
            new ProxyNode(name: 'proxy-node9',weight: 8),
            new ProxyNode(name: 'proxy-node10',weight: 4),
        ]);
        
$cluster->sort("desc");
```
The probability of choosing nodes for Frequency can be visualized as follows::
```text
+--------------+--------+
| nodes        | chance |
+--------------+--------+
| proxy-node1  | 40%    |
| proxy-node2  | 40%    |
+--------------+--------+
| proxy-node3  | 2.5%   |
| proxy-node8  | 2.5%   |
| proxy-node5  | 2.5%   |
| proxy-node6  | 2.5%   |
| proxy-node4  | 2.5%   |
| proxy-node7  | 2.5%   |
| proxy-node9  | 2.5%   |
| proxy-node10 | 2.5%   |
+-----------+-----------+
```

-------------

## Round Robin

The proxies will be rotated in turn ($counter : start counting from somewhere)
```php
use TruongBo\ProxyRotation;
use TruongBo\ProxyRotation\Strategy\RoundRobin;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$rotation = new Rotation(new RoundRobin(counter: 0));

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1'),
            new ProxyNode(name: 'proxy-node2'),
            new ProxyNode(name: 'proxy-node3'),
            new ProxyNode(name: 'proxy-node4'),
        ]);
```
Output: 
```text
+-------------+
| proxy-node1 |
| proxy-node2 |
| proxy-node3 |
| proxy-node4 |
| proxy-node1 |
| proxy-node2 |
| proxy-node3 |
| proxy-node4 |
| etc...      |
+-------------+
```
* You can interfere with proxy usage for a certain period of time if the proxy is restricted from use.Using [Max Use]
--------------------
## Weighted Round Robin

The number of times this proxy node is called is the weight parameter passed in the initialization of the ProxyNode
($counter : start counting from somewhere)
```php
use TruongBo\ProxyRotation;
use TruongBo\ProxyRotation\Strategy\WeightedRoundRobin;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$rotation = new Rotation(new WeightedRoundRobin(counter: 0));

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1', weight: 3),
            new ProxyNode(name: 'proxy-node2'),
            new ProxyNode(name: 'proxy-node3', weight: 1),
            new ProxyNode(name: 'proxy-node4', weight: 1),
        ]);
```
Output:
```text
+-------------+
| proxy-node1 |
| proxy-node1 |
| proxy-node1 |
| proxy-node3 |
| proxy-node4 |
| etc...      |
+-------------+
```
* Proxy Node without weight will not be used

-------------------

## Multiple Dynamic

Dynamically change strategies according to the passed callable condition (Absolutely do not use if you do not know about it)

```php
use TruongBo\ProxyRotation;
use TruongBo\ProxyRotation\Strategy\WeightedRoundRobin;
use TruongBo\ProxyRotation\ProxyServer\ProxyCluster;
use TruongBo\ProxyRotation\ProxyServer\ProxyNode;

$rotation = new Rotation(new MultipleDynamic(
            new RoundRobin(counter: 0),
            new Random(input_random: "has_weight"),
            new WeightedRoundRobin(counter: 0),
        ));

$proxy_cluster = new ProxyCluster(
        cluster_name: 'cluster1', 
        array_proxy_node: [
            new ProxyNode(name: 'proxy-node1', weight: 3),
            new ProxyNode(name: 'proxy-node2'),
            new ProxyNode(name: 'proxy-node3', weight: 1),
            new ProxyNode(name: 'proxy-node4', weight: 1),
        ]);

        while (true) {
            $node = $rotation->pick($cluster, function (ProxyNode $proxy_node){
                //condition here
            });

            echo $node?->name;
        }
```
-------------------
