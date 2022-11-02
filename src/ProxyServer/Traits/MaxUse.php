<?php

namespace TruongBo\ProxyRotation\ProxyServer\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait MaxUse
{
    /**
     * Check max use proxy
     *
     * @param string $class_name
     * @return bool
     */
    public function hasCheckMaxUse(string $class_name): bool
    {
        if (!is_null($this->max_use) && !is_null($this->max_wait_use)) {
            $this->counter(class_name: $class_name);
            return true;
        }
        return false;
    }

    /**
     * Get counter proxy node by cache
     *
     * @param string $class_name
     * @return array
     */
    public function getCounter(string $class_name): array
    {
        $cache_name = config('proxy.cache_by_class') ? $class_name . "_" . $this->name : $this->name;
        if (Cache::has($cache_name)) {
            return Cache::get($cache_name);
        }
        return [];
    }

    /**
     * If you set values for $max_use and $max_wait_use, when this proxy runs pass the allowed number of times.
     * It will temporarily sleep in $max_wait_use and resume normal use
     *
     * @param string $class_name
     * @return void
     */
    private function counter(string $class_name): void
    {
        $cache_name = config('proxy.cache_by_class') ? $class_name . "_" . $this->name : $this->name;

        if (Cache::has($cache_name)) {
            $counter = Cache::get($cache_name)['counter'] ?? 1;
            $time = Cache::get($cache_name)['time'];
            Cache::put($cache_name, [
                'counter' => $counter + 1,
                'time' => $time
            ], config('proxy.time_cache_proxy_counter'));
        } else {
            Cache::put($cache_name, [
                'counter' => 1,
                'time' => null,
            ], config('proxy.time_cache_proxy_counter'));
        }
    }

    /**
     * True if the number of times to use the proxy has been exceeded and vice versa
     *
     * @param string $class_name
     * @return bool
     */
    public function checkCounter(string $class_name): bool
    {
        $cache_name = config('proxy.cache_by_class') ? $class_name . "_" . $this->name : $this->name;

        $proxy_node = Cache::get($cache_name);
        if ($proxy_node['counter'] >= $this->max_use) {
            if (is_null($proxy_node['time'])) {
                Cache::put($cache_name, [
                    'counter' => $proxy_node['counter'],
                    'time' => Carbon::now()->addSeconds($this->max_wait_use)->toDate()
                ], config('proxy.time_cache_proxy_counter'));
            } elseif (Carbon::parse($proxy_node['time'])->lt(Carbon::now())) {
                Cache::forget($cache_name);
                return false;
            }
            return true;
        }
        return false;
    }
}
