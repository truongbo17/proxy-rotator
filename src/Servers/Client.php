<?php

namespace TruongBo\ProxyRotation\Servers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use TruongBo\ProxyRotation\Exception\EmptyHostException;

final class Client
{
    public array $hosts = [];

    private HostInterface $current_host;

    private int $counter = 0;

    /**
     * Construct function class Client
     *
     * @param array $config
     * @param HostInterface ...$host
     * @throws EmptyHostException
     */
    public function __construct(
        public array          $config,
        HostInterface         ...$host
    )
    {
        if (count($host) < 1) {
            throw new EmptyHostException();
        }

        $this->hosts = $host;
        $this->setCurrentHost();

        if (!isset($this->config['handler'])) {
            $this->config['handler'] = HandlerStack::create();
        }
        if ($this->config['handler'] instanceof HandlerStack) {
            $this->config['handler']->push($this->getRetryMiddleware(current_host: $this->current_host));
        }
    }

    /**
     * Send request
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(): ResponseInterface
    {
        $client = new GuzzleClient([
            'timeout' => $this->current_host->time_out,
            ...$this->config
        ]);

        return $client->request(
            $this->current_host->method,
            $this->current_host->endpoint,
            $this->current_host->options
        );
    }

    /**
     * Set current host
     *
     * @return void
     */
    private function setCurrentHost(): void
    {
        $this->current_host = $this->getCurrentHost();
    }

    /**
     * Get current host by index
     *
     * @return HostInterface
     * */
    private function getCurrentHost(): HostInterface
    {
        $index = $this->counter++ % count($this->hosts);
        return $this->hosts[$index];
    }

    private function getRetryMiddleware(HostInterface $current_host): callable
    {
        return Middleware::retry(
            function (
                int                $retries,
                RequestInterface   $request,
                ?ResponseInterface $response = null,
                ?\RuntimeException $e = null
            ) use ($current_host) {
                if ($retries >= $current_host->retry_fail_to_next) {
                    return false;
                }

                if ($response && in_array($response->getStatusCode(), (array)$current_host->status_code_to_next)) {
                    return true;
                }

                if ($e instanceof ConnectException && $current_host->check_exception) {
                    return true;
                }

                return false;
            }
        );
    }

}
