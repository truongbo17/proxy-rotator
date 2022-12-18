# Multiple Endpoint

### Description

* Suppose you have multiple api points to get similar data.
* Using this package it will help you to send requests to api endpoints one after another check through the allowed error codes to retry.
* When limit expires to try again it will pass to other endpoints to send requests and so on in turn.

### Basic usage 

* Send request :
```php
use TruongBo\ProxyRotation\Servers\Client;
use TruongBo\ProxyRotation\Servers\Host;

//Assuming this host is not real
$first_host = new Host(
    endpoint: "https://api.tiktokkkkkk.com/trending/get",
    method: "GET",
    retry_fail_to_next: 3,
);

//Assuming this server timed out (249)
$second_host = new Host(
    endpoint: "https://api.tiktok.com/trending/get",
    method: "GET",
    retry_fail_to_next: 2,
);

//Assuming this host is usable and returns data
$third_host = new Host(
    endpoint: "https://apih2.tiktok.com/trending/get",
    method: "GET",
    retry_fail_to_next: 3,
);

//Send request
$client = new Client(
    config: [
        'stop_when_run_all' => true
    ],
    hosts: $first_host, $second_host, $third_host
);

/**
 * @return Psr\Http\Message\ResponseInterface;
 * */
$response = $client->send();
```

* Debug running :

```php
Host name : "https://api.tiktokkkkkk.com/trending/get" => Exception no host (try : 1)
Host name : "https://api.tiktokkkkkk.com/trending/get" => Exception no host (try : 2)
Host name : "https://api.tiktokkkkkk.comtrending/get" => Exception no host (try : 3)

Host name : "https://api.tiktok.com/trending/get" => Exception no host (try : 1)
Host name : "https://api.tiktok.com/trending/get" => Exception no host (try : 2)

Host name : "https://apih2.tiktok.com/trending/get" => Status Code 200 (try : 1)
=> Done 
```