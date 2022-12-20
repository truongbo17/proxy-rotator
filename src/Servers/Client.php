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
     * Debug see which host guzzle is sending request to
     * @var bool $debug
     * */
    private bool $debug = false;

    /**
     * If the request has been sent to all servers and failed, you can repeat it by setting $stop_when_run_all to false
     * @var bool $stop_when_run_all
     * */
    private bool $stop_when_run_all = true;

    /**
     * @var int $sleep_time_to_next_host
     * */
    private int $sleep_time_to_next_host = 0;

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
        if (isset($config['stop_when_run_all']) && is_bool($config['stop_when_run_all'])) {
            $this->stop_when_run_all = $this->config['stop_when_run_all'];
        }

        //debug
        if (isset($config['debug']) && is_bool($config['debug'])) {
            $this->debug = $this->config['debug'];
        }

        //set sleep time next to request
        if (isset($config['sleep_to_next']) && is_numeric($config['sleep_to_next'])) {
            $this->sleep_time_to_next_host = $this->config['sleep_to_next'];
        }

        //handler stack config
        if (!isset($this->config['handler'])) {
            $this->config['handler'] = HandlerStack::create();
        }
        if ($this->config['handler'] instanceof HandlerStack) {
            $this->config['handler']->push($this->getRetryMiddleware());
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
     * If it's true, try again, if it's wrong, stop and switch to another host endpoint
     *
     * @return callable
     */
    private function getRetryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                int                $retries,
                RequestInterface   $request,
                ?ResponseInterface $response = null,
                ?RuntimeException  $e = null
            ) {
                $this->current_host->sleep();

                DebugSendingRequest::debug(
                    is_debug: $this->debug,
                    host: $this->current_host,
                    response: $response,
                    exception: $e,
                    try_time: $retries + 1
                );

                foreach ($this->current_host->getRetriesLogic() as $retry_callable) {
                    $result_call_func = call_user_func(
                        $retry_callable,
                        $this->current_host,
                        $retries,
                        $request,
                        $response,
                        $e
                    );
                    if (is_bool($result_call_func)) return $result_call_func;
                }

                return $this->baseRetry($retries, $response, $e);
            }
        );
    }

    /**
     * Base retry middleware request
     *
     * @param int $retries
     * @param $response
     * @param $e
     * @return bool
     */
    private function baseRetry(int $retries, $response, $e): bool
    {
        if ($retries + 1 >= $this->current_host->retry_fail_to_next) {
            return false;
        }

        if ($response && in_array($response->getStatusCode(), (array)$this->current_host->status_code_to_next)) {
            return true;
        }

        if ($e instanceof ConnectException && $this->current_host->check_exception) {
            return true;
        }

        return false;
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

            sleep($this->sleep_time_to_next_host);
            goto re_send_request;
        }
    }

}
