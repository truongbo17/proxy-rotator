<?php

namespace TruongBo\ProxyRotation\Servers;

use Carbon\Carbon;

class DebugSendingRequest
{
    /**
     * Debug when sending request
     *
     * @param bool $is_debug
     * @param HostInterface $host
     * @param $response
     * @param $exception
     * @param int $try_time
     */
    public static function debug(bool $is_debug, HostInterface $host, $response, $exception, int $try_time): void
    {
        if ($is_debug) {
            dump("-----------------------------------------------------------------------");
            dump("Now : " . Carbon::now()->format('H:i:s d/m/Y'));
            dump("Guzzle is sending request to : {$host->endpoint} with time : $try_time");
            dump("Status Code : " . $response?->getStatusCode());
            dump("Exception : " . $exception?->getMessage());
            dump("-----------------------------------------------------------------------");
        }
    }
}