<?php

namespace TruongBo\ProxyRotation\Servers;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TruongBo\ProxyRotation\Exception\EmptyHostException;

final class Client
{
    /**
     * @var array $hosts
     * */
    public array $hosts = [];

    /**
     * @var HostInterface $current_host
     * */
    private HostInterface $current_host;

    /**
     * @var int $counter
     * */
    private int $counter = 0;

    /**
     * @var int $current_index
     */
    private int $current_index = 0;

    /**
     * @var bool $stop_when_run_all
     * If the request has been sent to all servers and failed, you can repeat it by setting $stop_when_run_all to false
     * */
    private bool $stop_when_run_all = true;

    /**
     * Construct function class Client
     *
     * @param array $config
     * @param HostInterface ...$hosts
     * @throws EmptyHostException
     */
    public function __construct(
        public array          $config,
        HostInterface         ...$hosts
    )
    {
        if (count($hosts) < 1) {
            throw new EmptyHostException();
        }

        $this->hosts = $hosts;
        $this->setCurrentHost();

        //stop when run all config
        if(isset($config['stop_when_run_all']) && is_bool($config['stop_when_run_all'])){
            $this->stop_when_run_all = $this->config['stop_when_run_all'];
        }

        //handler stack config
        if (!isset($this->config['handler'])) {
            $this->config['handler'] = HandlerStack::create();
        }
        if ($this->config['handler'] instanceof HandlerStack) {
            $this->config['handler']->push($this->getRetryMiddleware(current_host: $this->current_host));
        }
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
        $this->current_index = $index;
        return $this->hosts[$index];
    }

    /**
     * Handle retry request
     *
     * @param HostInterface $current_host
     * @return callable
     */
    private function getRetryMiddleware(HostInterface $current_host): callable
    {
        return Middleware::retry(
            function (
                int                $retries,
                RequestInterface   $request,
                ?ResponseInterface $response = null,
                ?RuntimeException  $e = null
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

    /**
     * Send request
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function send(): ResponseInterface
    {
        re_send_request:
        dump($this->current_host->endpoint);
        try {
            $client = new GuzzleClient([
                'timeout' => $this->current_host->time_out,
                ...$this->config
            ]);

            return $client->request(
                $this->current_host->method,
                $this->current_host->endpoint,
                $this->current_host->options
            );
        } catch (GuzzleException $exception) {
            $this->setCurrentHost();
            if ($this->stop_when_run_all && $this->current_index == 0) {
                throw new Exception($exception);
            }
            goto re_send_request;
        }
    }

}
