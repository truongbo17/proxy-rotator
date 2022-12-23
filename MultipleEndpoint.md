# Multiple Endpoint

### Description

* Suppose you have multiple api points to get similar data.
* Using this package it will help you to send requests to api endpoints one after another check through the allowed error codes to retry.
* When limit expires to try again it will pass to other endpoints to send requests and so on in turn.

### Basic usage 

#### Send request :
```php
use TruongBo\ProxyRotation\Servers\Client;
use TruongBo\ProxyRotation\Servers\Host;

//Assuming this host is not real
 $first_host = new Host(
            endpoint: "https://httpbin.test/ip",
            method: "GET",
            retry_fail_to_next: 2,
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
 * @return \Psr\Http\Message\ResponseInterface;
 * */
$response = $client->send();
```
----- 
* **Host constructor**
  * retry_fail_to_next : number of retry requests sent to the server (number)
  * time_out : time out connect to host (number)
  * check_exception : check with exception (bool)
  * status_code_to_next : status can be accepted for retry (eg 429 when you send multiple requests to the host)
  * options : option to Guzzle request

* **Client constructor**
  * config : config client (stop_when_run_all and HandlerStack and debug request sending to...)
  * hosts : TruongBo\ProxyRotation\Servers\Host

* **Add custom logic retry**
  * add callable to function addRetryLogic() with param `$current_host` , `$retries`, `$request`, `$response`, `$e` 
  ```php
    $first_host
            ->addRetryLogic(function (
                $current_host,
                $retries,
                $request,
                $response,
                $e
            ) {
                //Write condition check retry in here
                if ($retries + 1 >= $current_host->retry_fail_to_next) {
                    return false;
                }
                return true;
            });
    ```
-----
* **And if you want to use with ProxyRotator**
  * Example create handler stack in [ProxyRotation](README.md)
  * Hosts using strategy rotation are not related to each other
  ```php
    $stack = HandlerStack::create();
    $stack->push(new ProxyMiddleware(rotation: $rotation,proxy_cluster: $proxy_cluster));
  
    //add to config Client construct
    $client = new Client(
        config: [
            'handler' => $stack
        ],
        hosts: $first_host, $second_host, $third_host
    );
  ```
----- 

#### Debug running :
<p align="center">
  <img src="./debug-test-multiple.gif"  alt="Debug Test"/>
</p>